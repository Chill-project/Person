<?php

namespace Chill\PersonBundle\Entity;

use Symfony\Component\Validator\ExecutionContextInterface;
use Chill\MainBundle\Entity\Country;

/**
 * Person
 */
class Person {
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
    
    const GENRE_MAN = 'man';
    const GENRE_WOMAN = 'woman';

    /**
     * @var string
     */
    private $memo = '';

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

    
    
    
    public function __construct(\DateTime $opening = null) {
        $this->history = new \Doctrine\Common\Collections\ArrayCollection();
        
        if ($opening === null) {
            $opening = new \DateTime();
        }
        
        $this->open($opening);
        
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
        $this->name = $name;
        
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
     * This is used for translations
     * @return int
     */
    public function getGenreNumeric() {
        if ($this->getGenre() == self::GENRE_WOMAN)
            return 1;
        else 
            return 0;
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
     * @param Chill\MainBundle\Entity\Country $countryOfBirth
     * @return Person
     */
    public function setCountryOfBirth(Country $countryOfBirth = null)
    {   
        $this->countryOfBirth = $countryOfBirth;
        return $this;
    }

    /**
     * Get countryOfBirth
     *
     * @return Chill\MainBundle\Entity\Country 
     */
    public function getCountryOfBirth()
    {
        return $this->countryOfBirth;
    }

    /**
     * Set nationality
     *
     * @param Chill\MainBundle\Entity\Country $nationality
     * @return Person
     */
    public function setNationality(Country $nationality = null)
    {
        $this->nationality = $nationality;
    
        return $this;
    }

    /**
     * Get nationality
     *
     * @return Chill\MainBundle\Entity\Country 
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
}