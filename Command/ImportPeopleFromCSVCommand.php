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
                ->setHelp("Import people from a csv file. The first row must "
                        . "contains the header column and will determines where "
                        . "the value will be matched. \n"
                        . "Date format: the possible date format may be separated"
                        . "by an |. The possible format will be tryed from the first "
                        . "to the last. The format should be explained as "
                        . "http://php.net/manual/en/function.strftime.php")
                ->addArgument('locale', InputArgument::REQUIRED, "The locale to use in displaying translatable strings from entities")
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
                ->addOption('locale',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        "The locale, used in interpretation of date. You should enter the option as listed by the command locale -a",
                        "fr_FR.utf8")
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
    }
    
    protected function matchColumnToCustomField($row)
    {
        
        $cfMappingsOptions = $this->input->getOption('custom-field');
        /* @var $em \Doctrine\Common\Persistence\ObjectManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /* @var $helper \Chill\MainBundle\Templating\TranslatableStringHelper */
        $helper = $this->getContainer()->get('chill.main.helper.translatable_string');
        
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
            // skip if custom field does not exists
            if ($customField === NULL) {
                $this->logger->error("The custom field with slug $cfSlug could not be found. "
                        . "Stopping this command.");
                throw new \RuntimeException("The custom field with slug $cfSlug could not be found. "
                        . "Stopping this command.");
            } 
            
            $this->logger->notice(sprintf("Matched custom field %s (question : '%s') on column %d (displayed in the file as '%s')",
                    $customField->getSlug(), $helper->localize($customField->getName()), $rowNumber, $column));
            
            $this->customFieldMapping[$rowNumber] = $customField;
        }
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = new ConsoleLogger($output);
        $this->input = $input;
        $this->output = $output;
        
        $this->logger->debug("Setting locale to ".$input->getOption('locale'));
        setlocale(LC_TIME, $input->getOption('locale'));
        
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
                    $num ++;
                }

                $line ++;
                $this->line++;
            }
        } finally {
            $this->logger->debug('closing csv', array('method' => __METHOD__));
            fclose($csv);
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
            $this->logger->debug("skipping a closing date because opening date is after yesterday");
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
    
    protected function processingCustomFields(Person $person)
    {
        /* @var $factory \Symfony\Component\Form\FormFactory */
        $factory = $this->getContainer()->get('form.factory');
        /* @var $cfProvider \Chill\CustomFieldsBundle\Service\CustomFieldProvider */
        $cfProvider = $this->getContainer()->get('chill.custom_field.provider');
        
        /* @var $$customField \Chill\CustomFieldsBundle\Entity\CustomField */
        foreach($this->customFieldMapping as $rowNumber => $customField) {
            $builder = $factory->createBuilder();
            $cfProvider->getCustomFieldByType($customField->getType())
                    ->buildForm($builder, $customField);
            $form = $builder->getForm();
            var_dump($form);
        }
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
                        ($dateR['tm_mday']+1),
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
