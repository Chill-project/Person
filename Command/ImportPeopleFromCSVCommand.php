<?php

/*
 * Copyright (C) 2016 Champs-Libres <info@champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\PersonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\Table;
use Chill\PersonBundle\Entity\Person;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ImportPeopleFromCSVCommand extends ContainerAwareCommand
{
    /**
     *
     * @var InputInterface
     */
    protected $input;
    
    /**
     *
     * @var OutputInterface
     */
    protected $output;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     *
     * @var \Chill\MainBundle\Templating\TranslatableStringHelper
     */
    protected $helper;
    
    /**
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;
    
    /**
     * the line currently read
     *
     * @var int
     */
    protected $line;
    
    /**
     *
     * @var array where key are column names, and value the custom field slug
     */
    protected $customFieldMapping = array();
    
    /**
     * Contains an array of information searched in the file.
     * 
     * position 0: the information key (which will be used in this process)
     * position 1: the helper
     * position 2: the default value
     *
     * @var array
     */
    protected static $mapping = array(
        ['firstname',       'The column header for firstname',   'firstname'],
        ['lastname',        'The column header for lastname',  'lastname'],
        ['birthdate',       'The column header for birthdate', 'birthdate'],
        ['opening_date',    'The column header for opening date', 'opening_date'],
        ['closing_date',    'The column header for closing date', 'closing_date'],
        ['memo',            'The column header for memo',           'memo'],
        ['phonenumber',     'The column header for phonenumber',   'phonenumber']
    );
    
    /**
     * Different possible format to interpret a date
     *
     * @var string
     */
    protected static $defaultDateInterpreter = "%d/%m/%Y|%e/%m/%y|%d/%m/%Y|%e/%m/%Y";
                                                
    
    
    protected function configure()
    {
        $this->setName('chill:person:import')
                ->addArgument('csv_file', InputArgument::REQUIRED, "The CSV file to import")
                ->setDescription("Import people from a csv file")
                ->setHelp(<<<EOF
Import people from a csv file. The first row must contains the header column and will determines where the value will be matched. 

Date format: the possible date format may be separatedby an |. The possible format will be tryed from the first to the last. The format should be explained as http://php.net/manual/en/function.strftime.php                     
                        
php app/console chill:person:import /tmp/hepc.csv fr_FR.utf8  --firstname="Prénom" --lastname="Nom" --birthdate="D.N." --birthdate_format="%d/%m/%Y" --opening_date_format="%B %Y|%Y" --closing_date="der.contact" --closing_date_format="%Y" --custom-field="3=code" -vvv
EOF
            )
                ->addArgument('locale', InputArgument::REQUIRED, 
                        "The locale to use in displaying translatable strings from entities")
                ->addArgument('center', InputArgument::REQUIRED,
                        "The id of the center")
                ->addOption(
                        'force',
                        null,
                        InputOption::VALUE_NONE,
                        "Persist people in the database (default is not to persist people)"
                        )
                ->addOption(
                        'delimiter', 
                        'd', 
                        InputOption::VALUE_OPTIONAL, 
                        "The delimiter character of the csv file", 
                        ",")
                ->addOption(
                        'enclosure',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        "The enclosure character of the csv file",
                        '"'
                        )
                ->addOption(
                        'escape',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        "The escape character of the csv file",
                        "\\"
                        )
                ->addOption(
                        'length',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        "The length of line to read. 0 means unlimited.",
                        0
                        )
                ->addOption(
                        'dump-choice-matching',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        "The path of the file to dump the matching between label in CSV and answers"
                        )
                ->addOption(
                        'load-choice-matching',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        "The path of the file to load the matching between label in CSV and answers"
                        )
                ;
        
        // mapping columns
        foreach (self::$mapping as $m) {
            $this->addOptionShortcut($m[0], $m[1], $m[2]);
        }
        
        // other information
        $this->addOptionShortcut('birthdate_format', 'Format preference for '
                . 'birthdate. See help for date formats preferences.', 
                self::$defaultDateInterpreter);
        $this->addOptionShortcut('opening_date_format', 'Format preference for '
                . 'opening date. See help for date formats preferences.',
                self::$defaultDateInterpreter);
        $this->addOptionShortcut('closing_date_format', 'Format preference for '
                . 'closing date. See help for date formats preferences.', 
                self::$defaultDateInterpreter);
        
        // mapping column to custom fields
        $this->addOption('custom-field', NULL, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 
                "Mapping a column to a custom fields key. Example: 1=cf_slug");
        $this->addOption('skip-interactive-field-mapping', null, InputOption::VALUE_NONE,
                "Do not ask for interactive mapping");
    }
    
    /**
     * This function is a shortcut to addOption.
     * 
     * @param string $name
     * @param string $description
     * @param string $default
     * @return ImportPeopleFromCSVCommand
     */
    protected function addOptionShortcut($name, $description, $default)
    {
        $this->addOption($name, null, InputOption::VALUE_OPTIONAL, $description, $default);
        
        return $this;
    }
    
    
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // preparing the basic
        $this->input = $input;
        $this->output = $output;
        $this->logger = new ConsoleLogger($output);
        
        $csv = $this->openCSV();
        
        // getting the first row
        if (($row = fgetcsv(
                $csv, 
                $input->getOption('length'), 
                $input->getOption('delimiter'), 
                $input->getOption('enclosure'), 
                $input->getOption('escape'))) !== false) {
            
            try {
                $this->matchColumnToCustomField($row);
            } finally {
                $this->logger->debug('closing csv', array('method' => __METHOD__));
                fclose($csv);
            }
        }
        
        // load the matching between csv and label
        $this->loadAnswerMatching();
    }
    
    protected function matchColumnToCustomField($row)
    {
        
        $cfMappingsOptions = $this->input->getOption('custom-field');
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /* @var $this->helper \Chill\MainBundle\Templating\TranslatableStringHelper */
        $this->helper = $this->getContainer()->get('chill.main.helper.translatable_string');
        
        foreach($cfMappingsOptions as $cfMappingStringOption) {
            list($rowNumber, $cfSlug) = preg_split('|=|', $cfMappingStringOption);
            
            // check that the column exists, getting the column name
            $column = $row[$rowNumber];
            
            if (empty($column)) {
                $message = "The column with row $rowNumber is empty.";
                $this->logger->error($message);
                throw new \RuntimeException($message);
            }
            
            // check a custom field exists
            try {
                $customField = $em->createQuery("SELECT cf "
                        . "FROM ChillCustomFieldsBundle:CustomField cf "
                        . "JOIN cf.customFieldGroup g "
                        . "WHERE cf.slug = :slug "
                        . "AND g.entity = :entity")
                        ->setParameters(array(
                            'slug' => $cfSlug,
                            'entity' => Person::class
                        ))
                        ->getSingleResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
                $message = sprintf(
                        "The customfield with slug '%s' does not exists. It was associated with column number %d",
                        $cfSlug,
                        $rowNumber
                        );
                $this->logger->error($message);
                throw new \RuntimeException($message);
            }
            // skip if custom field does not exists
            if ($customField === NULL) {
                $this->logger->error("The custom field with slug $cfSlug could not be found. "
                        . "Stopping this command.");
                throw new \RuntimeException("The custom field with slug $cfSlug could not be found. "
                        . "Stopping this command.");
            } 
            
            $this->logger->notice(sprintf("Matched custom field %s (question : '%s') on column %d (displayed in the file as '%s')",
                    $customField->getSlug(), $this->helper->localize($customField->getName()), $rowNumber, $column));
            
            $this->customFieldMapping[$rowNumber] = $customField;
        }
    }
    
    /**
     * Load the mapping between answer in CSV and value in choices from a json file
     */
    protected function loadAnswerMatching()
    {
        if ($this->input->hasOption('load-choice-matching')) {
            $fs = new Filesystem();
            $filename = $this->input->getOption('load-choice-matching');

            if (!$fs->exists($filename)) {
                $this->logger->warning("The file $filename is not found. Choice matching not loaded");
            } else {
                $this->logger->debug("Loading $filename as choice matching");
                $this->cacheAnswersMapping = \json_decode(\file_get_contents($filename), true);
            }
        }
    }
    
    protected function dumpAnswerMatching()
    {
        if ($this->input->hasOption('dump-choice-matching')) {
            $this->logger->debug("Dump the matching between answer and choices");
            $str = json_encode($this->cacheAnswersMapping, JSON_PRETTY_PRINT);
            
            $fs = new Filesystem();
            $filename = $this->input->getOption('dump-choice-matching');
            
            $fs->dumpFile($filename, $str);
        }
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = new ConsoleLogger($output);
        $this->input = $input;
        $this->output = $output;
        
        $this->logger->debug("Setting locale to ".$input->getArgument('locale'));
        setlocale(LC_TIME, $input->getArgument('locale'));
        
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        // opening csv as resource
        $csv = $this->openCSV();
        
        $num = 0;
        $line = $this->line = 1;
        
        try {
            while (($row = fgetcsv(
                    $csv, 
                    $input->getOption('length'), 
                    $input->getOption('delimiter'), 
                    $input->getOption('enclosure'), 
                    $input->getOption('escape'))) !== false) {
                $this->logger->debug("Processing line ".$this->line);
                if ($line === 1 ) {
                    $this->logger->debug('Processing line 1, headers');

                    $headers = $this->processingHeaders($row);
                } else {
                    $person = $this->createPerson($row, $headers);
                    $this->processingCustomFields($person, $row);
                    
                    if ($this->input->getOption('force') === TRUE) {
                        $this->em->persist($person);
                    }
                    
                    $num ++;
                }

                $line ++;
                $this->line++;
            }
            
            if ($this->input->getOption('force') === true) {
                $this->logger->debug('persisting entitites');
                $this->em->flush();
            }
        } finally {
            $this->logger->debug('closing csv', array('method' => __METHOD__));
            fclose($csv);
            // dump the matching between answer and choices
            $this->dumpAnswerMatching();
        }
    }
    
    /**
     * 
     * @return resource
     * @throws \RuntimeException
     */
    protected function openCSV()
    {
        $fs = new Filesystem();
        $filename = $this->input->getArgument('csv_file');
        
        if (!$fs->exists($filename)) {
            throw new \RuntimeException("The file does not exists or you do not "
                    . "have the right to read it.");
        }
        
        $resource = fopen($filename, 'r');
        
        if ($resource == FALSE) {
            throw new \RuntimeException("The file '$filename' could not be opened.");
        }
        
        return $resource;
    }
    
    /**
     * 
     * @param type $firstRow
     * @return array where keys are column number, and value is information mapped
     */
    protected function processingHeaders($firstRow)
    {
        $availableOptions = array_map(function($m) { return $m[0]; }, self::$mapping);
        $matchedColumnHeaders = array();
        $headers = array();
        
        foreach($availableOptions as $option) {
            $matchedColumnHeaders[$option] = $this->input->getOption($option);
        }
        
        foreach($firstRow as $key => $content) {
            $content = trim($content);
            if (in_array($content, $matchedColumnHeaders)) {
                $information = array_search($content, $matchedColumnHeaders);
                $headers[$key] = $information;
                $this->logger->notice("Matched $information on column $key (displayed in the file as '$content')");
            } else {
                $this->logger->notice("Column with content '$content' is ignored");
            }
        } 
        
        return $headers;
    }
    
    /**
     * 
     * @param array $row
     * @param array $headers the processed header : an array as prepared by self::processingHeaders
     * @return Person
     */
    protected function createPerson($row, $headers)
    {
        // trying to get the opening date
        $openingDateString = trim($row[array_search('opening_date', $headers)]);
        $openingDate = $this->processDate($openingDateString, $this->input->getOption('opening_date_format'));
        
        $person = $openingDate instanceof \DateTime ? new Person($openingDate) : new Person();
        
        // currently, import only men
        $person->setGender(Person::MALE_GENDER);
        
        // add the center
        $center = $this->em->getRepository('ChillMainBundle:Center')
                ->find($this->input->getArgument('center'));
        $person->setCenter($center);
        
        foreach($headers as $column => $info) {
            
            $value = trim($row[$column]);
            
            switch($info) {
                case 'firstname':
                    $person->setFirstName($value);
                    break;
                case 'lastname':
                    $person->setLastName($value);
                    break;
                case 'birthdate':
                    $this->processBirthdate($person, $value);
                    break;
                case 'opening_date':
                    // we have processed this when creating the person object, skipping;
                    break;
                case 'closing_date':
                    $this->processClosingDate($person, $value);
                    break;
                case 'memo':
                    $person->setMemo($value);
                    break;
                case 'phonenumber':
                    $person->setPhonenumber($value); 
                    break;
            }
        }
        
        return $person;
    }
    
    
    protected function processBirthdate(Person $person, $value)
    {
        $date = $this->processDate($value, $this->input->getOption('birthdate_format'));
        
        if ($date instanceof \DateTime) {
            // we correct birthdate if the date is in the future
            // the most common error is to set date 100 years to late (ex. 2063 instead of 1963)
            if ($date > new \DateTime('yesterday')) {
                $date = $date->sub(new \DateInterval('P100Y'));
            }
            
            $person->setBirthdate($date);
            
            return;
        }
        
        // if we arrive here, we could not process the date
        $this->logger->warning(sprintf(
                "Line %d : the birthdate could not be interpreted. Was %s.",
                $this->line,
                $value));
        
    }
    
    protected function processClosingDate(Person $person, $value)
    {
        // we skip if the opening date is now (or after yesterday)
        /* @var $period \Chill\PersonBundle\Entity\AccompanyingPeriod */
        $period = $person->getCurrentAccompanyingPeriod();
        
        if ($period->getOpeningDate() > new \DateTime('yesterday')) {
            $this->logger->debug(sprintf("skipping a closing date because opening date is after yesterday (%s)",
                    $period->getOpeningDate()->format('Y-m-d')));
            return;
        }
        
        
        $date = $this->processDate($value, $this->input->getOption('closing_date_format'));
        
        if ($date instanceof \DateTime) {
            // we correct birthdate if the date is in the future
            // the most common error is to set date 100 years to late (ex. 2063 instead of 1963)
            if ($date > new \DateTime('yesterday')) {
                $date = $date->sub(new \DateInterval('P100Y'));
            }
            
            $period->setClosingDate($date);
            $person->close();
            return;
        }
        
        // if we arrive here, we could not process the date
        $this->logger->warning(sprintf(
                "Line %d : the closing date could not be interpreted. Was %s.",
                $this->line,
                $value));
    }
    
    protected function processingCustomFields(Person $person, $row)
    {
        /* @var $factory \Symfony\Component\Form\FormFactory */
        $factory = $this->getContainer()->get('form.factory');
        /* @var $cfProvider \Chill\CustomFieldsBundle\Service\CustomFieldProvider */
        $cfProvider = $this->getContainer()->get('chill.custom_field.provider');
        $cfData = array();
        
        /* @var $$customField \Chill\CustomFieldsBundle\Entity\CustomField */
        foreach($this->customFieldMapping as $rowNumber => $customField) {
            $builder = $factory->createBuilder();
            $cfProvider->getCustomFieldByType($customField->getType())
                    ->buildForm($builder, $customField);
            $form = $builder->getForm();
            
            // get the type of the form
            $type = get_class($form->get($customField->getSlug())
                    ->getConfig()->getType()->getInnerType());
            $this->logger->debug(sprintf("Processing a form of type %s", 
                    $type));

            switch ($type) {
                case \Symfony\Component\Form\Extension\Core\Type\TextType::class:
                    $cfData[$customField->getSlug()] = 
                        $this->processTextType($row[$rowNumber], $form, $customField);
                    break;
                case \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class:
                case \Chill\MainBundle\Form\Type\Select2ChoiceType::class:
                    $cfData[$customField->getSlug()] =
                        $this->processChoiceType($row[$rowNumber], $form, $customField);
            }
            
        }
        
        $person->setCFData($cfData);
    }
    
    /**
     * Process a text type on a custom field
     * 
     * @param type $value
     * @param \Chill\PersonBundle\Command\Symfony\Component\Form\FormInterface $form
     */
    protected function processTextType(
            $value, 
            \Symfony\Component\Form\FormInterface $form, 
            \Chill\CustomFieldsBundle\Entity\CustomField $cf
            )
    {
        $form->submit(array($cf->getSlug() => $value));
        
        $value = $form->getData()[$cf->getSlug()];
        
        $this->logger->debug(sprintf("Found value : %s for custom field with question "
                . "'%s'", $value, $this->helper->localize($cf->getName())));
        
        return $value;
    }
    
    protected $cacheAnswersMapping = array();
    
    
    /**
     * Process a custom field choice.
     * 
     * The method try to guess if the result exists amongst the text of the possible
     * choices. If the texts exists, then this is picked. Else, ask the user.
     * 
     * @param string $value
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Chill\CustomFieldsBundle\Entity\CustomField $cf
     * @return string
     */
    protected function processChoiceType(
            $value,
            \Symfony\Component\Form\FormInterface $form, 
            \Chill\CustomFieldsBundle\Entity\CustomField $cf
            )
    {
        // getting the possible answer and their value :
        $view = $form->get($cf->getSlug())->createView();
        $answers = $this->collectChoicesAnswers($view->vars['choices']);
        
        // if we do not have any answer on the question, throw an error.
        if (count($answers) === 0) {
            $message = sprintf(
                    "The question '%s' with slug '%s' does not count any answer.",
                    $this->helper->localize($cf->getName()),
                    $cf->getSlug()
                    );
            
            $this->logger->error($message, array(
                'method' => __METHOD__,
                'slug' => $cf->getSlug(),
                'question' => $this->helper->localize($cf->getName())
            ));
            
            throw new \RuntimeException($message);
        }
        
        if ($view->vars['required'] === false) {
            $answers[null] = '** no answer';
        }
        
        // the answer does not exists in cache. Try to find it, or asks the user
        if (!isset($this->cacheAnswersMapping[$cf->getSlug()][$value])) {
            
            // try to find the answer (with array_keys and a search value
            $values = array_keys(
                    array_map(function($label) { return trim(strtolower($label)); }, $answers), 
                    trim(strtolower($value)),
                    true
                    );
            
            if (count($values) === 1) {
                // we could guess an answer !
                $this->logger->info("This question accept multiple answers");
                $this->cacheAnswersMapping[$cf->getSlug()][$value] = 
                        $view->vars['multiple'] == false ? $values[0] : array($values[0]);
                $this->logger->info(sprintf("Guessed that value '%s' match with key '%s' "
                        . "because the CSV and the label are equals.",
                        $value, $values[0]));
            } else {
                // we could nog guess an answer. Asking the user.
                $this->output->writeln("<info>I do not know the answer to this question : </info>");
                $this->output->writeln($this->helper->localize($cf->getName()));

                // printing the possible answers
                /* @var $table \Symfony\Component\Console\Helper\Table */
                $table = new Table($this->output);
                $table->setHeaders(array('#', 'label', 'value'));
                $i = 0;

                foreach($answers as $key => $answer) {
                    $table->addRow(array(
                        $i, $answer, $key
                    ));
                    $matchingTableRowAnswer[$i] = $key;
                    $i++;
                }
                $table->render($this->output);

                $question = new ChoiceQuestion(
                        sprintf('Please pick your choice for the value "%s"', $value),
                        array_keys($matchingTableRowAnswer)
                        );
                $question->setErrorMessage("This choice is not possible");
                
                if ($view->vars['multiple']) {
                    $this->logger->debug("this question is multiple");
                    $question->setMultiselect(true);
                }
        
                $selected = $this->getHelper('question')->ask($this->input, $this->output, $question);

                $this->output->writeln(sprintf('You have selected "%s"', 
                    is_array($answers[$matchingTableRowAnswer[$selected]]) ? 
                        implode(',', $answers[$matchingTableRowAnswer[$selected]]) :
                        $answers[$matchingTableRowAnswer[$selected]])
                    );
                
                // recording value in cache
                $this->cacheAnswersMapping[$cf->getSlug()][$value] = $matchingTableRowAnswer[$selected];
                $this->logger->debug(sprintf("Setting the value '%s' in cache for customfield '%s' and answer '%s'",
                        is_array($this->cacheAnswersMapping[$cf->getSlug()][$value]) ?
                            implode(', ', $this->cacheAnswersMapping[$cf->getSlug()][$value]) :
                            $this->cacheAnswersMapping[$cf->getSlug()][$value],
                        $cf->getSlug(),
                        $value));
            }
        }
        var_dump($this->cacheAnswersMapping[$cf->getSlug()][$value]);
        $form->submit(array($cf->getSlug() => $this->cacheAnswersMapping[$cf->getSlug()][$value]));
        $value = $form->getData()[$cf->getSlug()];
        
        $this->logger->debug(sprintf(
                "Found value : %s for custom field with question '%s'", 
                is_array($value) ? implode(',', $value) : $value, 
                $this->helper->localize($cf->getName()))
                );
        
        return $value;
    }
    
    /**
     * Recursive method to collect the possibles answer from a ChoiceType (or 
     * its inherited types).
     * 
     * @param \Symfony\Component\Form\FormInterface $form
     * @return array where 
     */
    private function collectChoicesAnswers($choices)
    {
        $answers = array();
        
        /* @var $choice \Symfony\Component\Form\ChoiceList\View\ChoiceView */
        foreach($choices as $choice) {
            if ($choice instanceof \Symfony\Component\Form\ChoiceList\View\ChoiceView) {
                $answers[$choice->value] = $choice->label;
            } elseif ($choice instanceof \Symfony\Component\Form\ChoiceList\View\ChoiceGroupView) {
                $answers = $answers + $this->collectChoicesAnswers($choice->choices);
            } else {
                throw new \Exception(sprintf(
                        "The choice type is not know. Expected '%s' or '%s', get '%s'",
                        \Symfony\Component\Form\ChoiceList\View\ChoiceView::class,
                        \Symfony\Component\Form\ChoiceList\View\ChoiceGroupView::class,
                        get_class($choice)
                        ));
            }
        }
        
        return $answers;
    }
    
    
    
    protected function processDate($value, $formats)
    {
        $possibleFormats = explode("|", $formats);
        
        foreach($possibleFormats as $format) {
            $this->logger->debug("Trying format $format", array(__METHOD__));
            $dateR = strptime($value, $format);
            
            if (is_array($dateR) && $dateR['unparsed'] === '') {
                $string = sprintf("%04d-%02d-%02d %02d:%02d:%02d", 
                        ($dateR['tm_year']+1900),
                        ($dateR['tm_mon']+1),
                        ($dateR['tm_mday']),
                        ($dateR['tm_hour']),
                        ($dateR['tm_min']),
                        ($dateR['tm_sec']));
                $date = \DateTime::createFromFormat("Y-m-d H:i:s", $string);
                $this->logger->debug(sprintf("Interpreting %s as date %s", $value, $date->format("Y-m-d H:i:s")));
                
                return $date;
            }
        }
        
        // if we arrive here, we could not process the date
        $this->logger->debug(sprintf(
                "Line %d : a date could not be interpreted. Was %s.",
                $this->line,
                $value));
        
        return false;
    }
    
    
    
    
}
