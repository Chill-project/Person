<?php

namespace Chill\PersonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\ExecutionContextInterface;
use CL\CLHistoryBundle\Entity\IsHistoryContainer;
use CL\CLHistoryBundle\Entity\HasHistory;
use CL\CLHistoryBundle\Entity\HistoryHelper;

/**
 * Person
 */
class Person implements IsHistoryContainer, HasHistory {
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $surname;

    /**
     * @var \DateTime
     */
    private $dateOfBirth;

        /**
     * @var string
     */
    private $placeOfBirth = '';

    /**
     * @var string
     */
    private $genre;
    
    const GENRE_MAN = 'MAN';
    const GENRE_WOMAN = 'WOM';

    /**
     * @var string
     */
    private $civil_union = self::CIVIL_UNKNOW;
     /*Célibataire
Marié(e)
Veuf – Veuve
Séparé(e)
Divorcé(e)
Cohabitant légal
Indéterminé
ou une valeur vide lorsque la donnée nest pas connue*/
    const CIVIL_SINGLE = 'single';
    const CIVIL_WIDOW = 'widow';
    const CIVIL_SEPARATED = 'separated';
    const CIVIL_DIVORCED = 'divorced';
    const CIVIL_COHAB = 'cohab';
    const CIVIL_UNKNOW = 'unknow';

    /**
     * @var integer
     */
    private $nbOfChild = 0;

    /**
     * @var string
     */
    private $belgian_national_number;

    /**
     * @var string
     */
    private $memo = '';

    /**
     * @var string
     */
    private $address = '';

    /**
     * @var string
     */
    private $email = '';
    
    /**
     * @var \CL\Chill\MainBundle\Entity\Country
     */
    private $countryOfBirth;

    /**
     * @var \CL\Chill\MainBundle\Entity\Country
     */
    private $nationality;  
    
    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $history;
    
    /**
     *
     * @var boolean
     */
    private $proxyHistoryOpenState = false;
    
    
    const HISTORY_DOMAIN = 'person';
    
    /**
     *
     * @var string 
     */
    private $historyId = null;
    
    const ACTION_UPDATE = 'update';
    const ACTION_CREATE = 'create';
    
    
    
    public function __construct(\DateTime $opening = null) {
        $this->history = new \Doctrine\Common\Collections\ArrayCollection();
        
        if ($opening === null) {
            $opening = new \DateTime();
        }
        
        $this->open($opening);
        
        //create an helper with key "update", and set "creation" instead
        $this->getHistoryHelper(self::ACTION_UPDATE)
              ->setAction(self::ACTION_CREATE);
    }
    
    /**
     * 
     * @param \Chill\PersonBundle\Entity\PersonHistoryFile $history
     * @uses PersonHistoryFile::setPerson
     */
    public function addHistoryFile(PersonHistoryFile $history) {
        $history->setPerson($this);
        $this->history->add($history);
    }
    
    /**
     * set the Person file as open at the given date.
     * 
     * For updating a opening's date, you should update PersonHistoryFile instance
     * directly.
     * 
     * For closing a file, @see this::close
     * 
     * To check if the Person and his history is consistent, use validation.
     * 
     * @param \DateTime $date
     */
    public function open(\DateTime $date, $memo = '') {
        $history = new PersonHistoryFile($date);
        $history->setMemo($memo);
        $this->proxyHistoryOpenState = true;
        $this->addHistoryFile($history);
    }

    /**
     * 
     * Set the Person file as closed at the given date.
     * 
     * For update a closing date, you should update PersonHistoryFile instance 
     * directly.
     * 
     * To check if the Person and his history are consistent, use validation.
     * 
     * @param \DateTime $date
     * @param string $motive
     * @param string $memo
     * @throws \Exception if two lines of history are open.
     */
    public function close(\DateTime $date, $motive, $memo = '') {
        $histories = $this->history;
        
        $found = false;
        
        foreach ($histories as $history) {
            if ($history->isOpen()) {
                
                if ($found === true) {
                    throw new \Exception('two open line in history were found. This should not happen.');
                }
                
                $history->setDateClosing($date);
                $history->setMotive($motive);
                $history->setMemo($memo);
                $this->proxyHistoryOpenState = false;
                $found = true;
            }
        }
    }
    
    /**
     * 
     * @return null|PersonHistoryFile
     */
    public function getCurrentHistory() {
        if ($this->proxyHistoryOpenState === false) {
            return null;
        }
        
        foreach ($this->history as $history) {
            if ($history->isOpen()) {
                return $history;
            }
        }
    }
    
    /**
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getHistories() {
        return $this->history;
    }
    
    /**
     * 
     * @return PersonHistoryFile[]
     */
    public function getHistoriesOrdered() {
        $histories = $this->getHistories()->toArray();
        
        //order by date :
        usort($histories, function($a, $b) {
                    
                    $dateA = $a->getDateOpening();
                    $dateB = $b->getDateOpening();
                    
                    if ($dateA == $dateB) {
                        $dateEA = $a->getDateClosing();
                        $dateEB = $b->getDateClosing();
                        
                        if ($dateEA == $dateEB) {
                            return 0;
                        }
                        
                        if ($dateEA < $dateEB) {
                            return -1;
                        } else {
                            return +1;
                        }
                    }
                    
                    if ($dateA < $dateB) {
                        return -1 ;
                    } else {
                        return 1;
                    }
                });
                
                
        return $histories;
    }
    
    public function isOpen() {
        return $this->proxyHistoryOpenState;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Person
     */
    public function setName($name)
    {
        if ($name !== $this->name) {
            $this->getHistoryHelper(self::ACTION_UPDATE)
                  ->registerChange('name', $this->name, $name);
            $this->name = $name;
        }
        
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     * @return Person
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    
        return $this;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     * @return Person
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    
        return $this;
    }

    /**
     * Get dateOfBirth
     *
     * @return \DateTime 
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }


    /**
     * Set placeOfBirth
     *
     * @param string $placeOfBirth
     * @return Person
     */
    public function setPlaceOfBirth($placeOfBirth)
    {
        if ($placeOfBirth === null) {
            $placeOfBirth = '';
        }
        
        $this->placeOfBirth = $placeOfBirth;
    
        return $this;
    }

    /**
     * Get placeOfBirth
     *
     * @return string 
     */
    public function getPlaceOfBirth()
    {
        return $this->placeOfBirth;
    }

    /**
     * Set genre
     *
     * @param string $genre
     * @return Person
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
    
        return $this;
    }

    /**
     * Get genre
     *
     * @return string 
     */
    public function getGenre()
    {
        return $this->genre;
    }
    
    /**
     * return gender as a Numeric form.
     * Useful for translation :-)
     * @return int
     */
    public function getGenreNumeric() {
        if ($this->getGenre() == self::GENRE_WOMAN)
            return 1;
        else 
            return 0;
    }

    /**
     * Set civil_union
     *
     * @param string $civilUnion
     * @return Person
     */
    public function setCivilUnion($civilUnion)
    {
        if ($this->civil_union !== $civilUnion) {
            $this->getHistoryHelper(self::ACTION_UPDATE)
                  ->registerChange('civil_union', $this->civil_union, $civilUnion);
            $this->civil_union = $civilUnion;
        }
    
        return $this;
    }

    /**
     * Get civil_union
     *
     * @return string 
     */
    public function getCivilUnion()
    {
        return $this->civil_union;
    }

    /**
     * Set nbOfChild
     *
     * @param integer $nbOfChild
     * @return Person
     */
    public function setNbOfChild($nbOfChild)
    {
        $this->nbOfChild = $nbOfChild;
    
        return $this;
    }

    /**
     * Get nbOfChild
     *
     * @return integer 
     */
    public function getNbOfChild()
    {
        return $this->nbOfChild;
    }

    /**
     * Set belgian_national_number
     *
     * @param string $belgianNationalNumber
     * @return Person
     */
    public function setBelgianNationalNumber($belgianNationalNumber)
    {
        if ($belgianNationalNumber === null) {
            $belgianNationalNumber = '';
        }
        
        $this->belgian_national_number = $belgianNationalNumber;
    
        return $this;
    }

    /**
     * Get belgian_national_number
     *
     * @return string 
     */
    public function getBelgianNationalNumber()
    {
        return $this->belgian_national_number;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return Person
     */
    public function setMemo($memo)
    {
        if ($memo === null) {
            $memo = '';
        }
        
        if ($this->memo !== $memo) {
            $this->getHistoryHelper(self::ACTION_UPDATE)
                  ->registerChange('memo', $this->memo, $memo);
            $this->memo = $memo;
            
        }
        
        
    
        return $this;
    }

    /**
     * Get memo
     *
     * @return string 
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Person
     */
    public function setAddress($address)
    {
        if ($address === null) {
            $address = '';
        }
        
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Person
     */
    public function setEmail($email)
    {
        if ($email === null) {
            $email = '';
        }
        
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set countryOfBirth
     *
     * @param \CL\Chill\MainBundle\Entity\Country $countryOfBirth
     * @return Person
     */
    public function setCountryOfBirth(\CL\Chill\MainBundle\Entity\Country $countryOfBirth = null)
    {
        if ($this->countryOfBirth->getId() !== $countryOfBirth->getId()) {
            $this->getHistoryHelper(self::ACTION_UPDATE)
                  ->registerChange('country_of_birth', 
                        $this->countryOfBirth->getLabel(), 
                        $countryOfBirth->getLabel());
            
            $this->countryOfBirth = $countryOfBirth;
        }
        
    
        return $this;
    }

    /**
     * Get countryOfBirth
     *
     * @return \CL\Chill\MainBundle\Entity\Country 
     */
    public function getCountryOfBirth()
    {
        return $this->countryOfBirth;
    }

    /**
     * Set nationality
     *
     * @param \CL\Chill\MainBundle\Entity\Country $nationality
     * @return Person
     */
    public function setNationality(\CL\Chill\MainBundle\Entity\Country $nationality = null)
    {
        $this->nationality = $nationality;
    
        return $this;
    }

    /**
     * Get nationality
     *
     * @return \CL\Chill\MainBundle\Entity\Country 
     */
    public function getNationality()
    {
        return $this->nationality;
    }
    
    public function getLabel() {
        return $this->getSurname()." ".$this->getName();
    }
    
    public function __toString() {
        return $this->getLabel();
    }
    
    
    
    // VALIDATION
    
    
    public function isHistoryValid(ExecutionContextInterface $context) {
        $r = $this->checkHistoryIsNotCovering();
        
        
        if ($r !== true) {
            
            if ($r['result'] === self::ERROR_OPENING_NOT_CLOSED_IS_BEFORE_NEW_LINE) {
                $context->addViolationAt('history',
                        'validation.Person.constraint.history.open_history_without_closing',
                        array() );
                return;
            } 
            
            $context->addViolationAt('history', 
                   'validation.Person.constraint.history.opening_is_before_closing',
                   array(
                       '%dateOpening%' => $r['dateOpening']->format('d-m-Y'),
                       '%dateClosing%' => $r['dateClosing']->format('d-m-Y'),
                       '%date%' => $r['date']->format('d-m-Y')
                       )
                   );
            
            
            
        }
    }
    
    

    const ERROR_OPENING_IS_INSIDE_CLOSING = 1;
    const ERROR_OPENING_NOT_CLOSED_IS_BEFORE_NEW_LINE = 2;
    const ERROR_OPENING_NOT_CLOSE_IS_INSIDE_CLOSED_HISTORY_LINE = 3;
    const ERROR_OPENING_IS_BEFORE_OTHER_LINE_AND_CLOSED_IS_AFTER_THIS_LINE = 4; 
    
    public function checkHistoryIsNotCovering() {
        
        $histories = $this->getHistoriesOrdered();
        
                
         //check order :
         $oldOpening = array();
         $oldClosing = array();
         $i = 0; 
         
         foreach ($histories as $key => $history) {
             //history is open : we must check the arent any history after
             if ($history->isOpen()) {
                 foreach ($histories as $subKey => $against) {
                     //if we are checking the same, continue
                     if ($key === $subKey) {
                         continue;
                     }
                     
                     if ($history->getDateOpening() > $against->getDateOpening()
                             && $history->getDateOpening() < $against->getDateOpening()) {
                         // the history date opening is inside another opening line
                         return array(
                            'result' => self::ERROR_OPENING_NOT_CLOSE_IS_INSIDE_CLOSED_HISTORY_LINE,
                            'dateOpening' => $against->getDateOpening(), 
                            'dateClosing' => $against->getDateClosing(),
                            'date' => $history->getDateOpening()
                                );
                     }
                     
                     if ($history->getDateOpening() < $against->getDateOpening()
                             && $history->getDateClosing() > $against->getDateClosing()) {
                         // the history date opening is inside another opening line
                         return array(
                            'result' => self::ERROR_OPENING_IS_BEFORE_OTHER_LINE_AND_CLOSED_IS_AFTER_THIS_LINE,
                            'dateOpening' => $against->getDateOpening(), 
                            'dateClosing' => $against->getDateClosing(),
                            'date' => $history->getDateOpening()
                                );
                     }
                     
                     //if we have an aopening later...
                     if ($history->getDateOpening() < $against->getDateClosing()) {
                         return array( 'result' => self::ERROR_OPENING_NOT_CLOSED_IS_BEFORE_NEW_LINE,
                            'dateOpening' => $against->getDateOpening(), 
                            'dateClosing' => $against->getDateClosing(),
                            'date' => $history->getDateOpening()
                             );
                     }
                     
                     
                 }
                 
             } else {
                 //we must check there is not covering lines
                 
                 foreach ($histories as $subKey => $against) {
                     //check if dateOpening is inside an `against` line
                     if ($history->getDateOpening() > $against->getDateOpening()
                             && $history->getDateOpening() < $against->getDateClosing()) {
                           return array(
                                'result' => self::ERROR_OPENING_IS_INSIDE_CLOSING,
                                'dateOpening' => $against->getDateOpening(), 
                                'dateClosing' => $against->getDateClosing(),
                                'date' => $history->getDateOpening()
                                );
                     }
                 }
             }
         }
         
         return true;
        
    }

    public function getDomain() {
        return self::HISTORY_DOMAIN;
    }

    public function getHistoryId() {
        return $this->historyId;
    }

    public function setHistoryId($id) {
        $this->historyId = $id;
    }
    
    /**
     *
     * @var \CL\CLHistoryBundle\Entity\HistoryHelper 
     */
    private $historyHelper = array();
    
    
    private function getHistoryHelper($helper) {
        if (!isset($this->historyHelper[$helper])) {
            $this->historyHelper[$helper] = new HistoryHelper();
            
            $this->historyHelper[$helper]->setAction($helper);
        }
        
        return $this->historyHelper[$helper];
    }

    public function getEntityName() {
        return 'person';
    }

    public function getHistory() {
        $histories = array();
        
        foreach ($this->historyHelper as $historyHelper) {
            $histories = $histories->toArray();
        }
        
        return $histories;
    }

    public function getParentContainers() {
        return array($this);
    }

    public function getVersion() {
        return 0;
    }

}