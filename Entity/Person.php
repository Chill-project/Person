<?php

namespace Chill\PersonBundle\Entity;

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use Symfony\Component\Validator\ExecutionContextInterface;
use Chill\MainBundle\Entity\Country;
use Doctrine\Common\Collections\ArrayCollection;

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
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

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


    /**
     * The array where customfields data is stored
     * @var array
     */
    private $cFData;

    /**
     * @var string
     */
    private $phonenumber = '';

    /**
     * The spoken languages (ArrayCollection of Languages)
     * @var \Doctrine\Common\Collections\ArrayCollection
    */
    private $spokenLanguages;
    
    public function __construct(\DateTime $opening = null) {
        $this->history = new ArrayCollection();
        $this->spokenLanguages = new ArrayCollection();
        
        if ($opening === null) {
            $opening = new \DateTime();
        }
        
        $this->open(new PersonHistoryFile($opening));
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
    public function open(PersonHistoryFile $accompanyingPeriod) {
        $this->proxyHistoryOpenState = true;
        $this->addHistoryFile($accompanyingPeriod);
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
     * @param PersonHistoryFile
     * @throws \Exception if two lines of history are open.
     */
    public function close(PersonHistoryFile $accompanyingPeriod) 
    {
        $this->proxyHistoryOpenState = false;
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
     * Set firstName
     *
     * @param string $firstName
     * @return Person
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Person
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
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
        return $this->getFirstName()." ".$this->getLastName();
    }

    /**
     * Set cFData
     *
     * @param array $cFData
     *
     * @return Report
     */
    public function setCFData($cFData)
    {
        $this->cFData = $cFData;

        return $this;
    }

    /**
     * Get cFData
     *
     * @return array
     */
    public function getCFData()
    {
        return $this->cFData;
    }

    /**
     * Set phonenumber
     *
     * @param string $phonenumber
     * @return Person
     */
    public function setPhonenumber($phonenumber = '')
    {
        $this->phonenumber = $phonenumber;
    
        return $this;
    }

    /**
     * Get phonenumber
     *
     * @return string 
     */
    public function getPhonenumber()
    {
        return $this->phonenumber;
    }
    
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * Set spokenLanguages
     *
     * @param type $spokenLanguages
     * @return Person
     */
    public function setSpokenLanguages($spokenLanguages)
    {
        $this->spokenLanguages = $spokenLanguages;

        return $this;
    }

    /**
     * Get spokenLanguages
     *
     * @return ArrayCollection
     */
    public function getSpokenLanguages()
    {
        return $this->spokenLanguages;
    }
    
    // VALIDATION
    public function isHistoryValid(ExecutionContextInterface $context) {
        $r = $this->checkHistoryIsNotCovering();
        
        
        if ($r !== true) {
            
            if ($r['result'] === self::ERROR_OPENING_NOT_CLOSED_IS_BEFORE_NEW_LINE) {
                $context->addViolationAt('history',
                        'History not closed is before the new line',
                        array() );
                return;
            } 
            
            $context->addViolationAt('history', 
                   'Periods are collapsing',
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