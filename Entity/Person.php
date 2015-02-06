<?php

namespace Chill\PersonBundle\Entity;

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS,
 * <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
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
        
        $this->open(new AccompanyingPeriod($opening));
    }
    
    /**
     * 
     * @param \Chill\PersonBundle\Entity\AccompanyingPeriod $period
     * @uses AccompanyingPeriod::setPerson
     */
    public function addAccompanyingPeriod(AccompanyingPeriod $period) {
        $period->setPerson($this);
        $this->history->add($period);
    }
    
    public function removeHistoryFile(PersonHistoryFile $history) {
        $this->history->remove($history);
    }
    
    /**
     * set the Person file as open at the given date.
     * 
     * For updating a opening's date, you should update AccompanyingPeriod instance
     * directly.
     * 
     * For closing a file, @see this::close
     * 
     * To check if the Person and its accompanying period is consistent, use validation.
     * 
     * @param \Chill\PersonBundle\Entity\AccompanyingPeriod $accompanyingPeriod
     */
    public function open(AccompanyingPeriod $accompanyingPeriod) {
        $this->proxyHistoryOpenState = true;
        $this->addAccompanyingPeriod($accompanyingPeriod);
    }

    /**
     * 
     * Set the Person file as closed at the given date.
     * 
     * For update a closing date, you should update AccompanyingPeriod instance 
     * directly.
     * 
     * To check if the Person and its accompanying period are consistent, use validation.
     * 
     * @param AccompanyingPeriod
     * @throws \Exception if two lines of the accompanying period are open.
     */
    public function close(AccompanyingPeriod $accompanyingPeriod) 
    {
        $this->proxyHistoryOpenState = false;
    }
    
    /**
     * 
     * @return null|AccompanyingPeriod
     */
    public function getCurrentAccompanyingPeriod() {
        if ($this->proxyHistoryOpenState === false) {
            return null;
        }
        
        foreach ($this->history as $period) {
            if ($period->isOpen()) {
                return $period;
            }
        }
    }
    
    /**
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAccompanyingPeriods() {
        return $this->history;
    }
    
    /**
     * 
     * @return AccompanyingPeriod[]
     */
    public function getAccompanyingPeriodsOrdered() {
        $periods = $this->getAccompanyingPeriods()->toArray();
        
        //order by date :
        usort($periods, function($a, $b) {
                    
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
                
                
        return $periods;
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
        if ($this->getGenre() == self::GENRE_WOMAN) {
            return 1;
        } else {
            return 0;
        }
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
    public function isAccompanyingPeriodValid(ExecutionContextInterface $context) {
        $r = $this->checkAccompanyingPeriodIsNotCovering();
        
        if ($r !== true) {
            
            if ($r['result'] === self::ERROR_OPENING_NOT_CLOSED_IS_BEFORE_NEW_LINE) {
                $context->addViolationAt('history',
                        'Accompanying period not closed is before the new line',
                        array() );
                return;
            } 
            
            $context->addViolationAt('history', 'Periods are collapsing',
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
    const ERROR_OPENING_NOT_CLOSE_IS_INSIDE_CLOSED_ACCOMPANYING_PERIOD_LINE = 3;
    const ERROR_OPENING_IS_BEFORE_OTHER_LINE_AND_CLOSED_IS_AFTER_THIS_LINE = 4; 
    
    public function checkAccompanyingPeriodIsNotCovering()
    {    
        $periods = $this->getAccompanyingPeriodsOrdered();
        
        $i = 0; 
         
        foreach ($periods as $key => $period) {
            //accompanying period is open : we must check the arent any period after
            if ($period->isOpen()) {
                foreach ($periods as $subKey => $against) {
                    //if we are checking the same, continue
                    if ($key === $subKey) {
                        continue;
                    }
                     
                    if ($period->getDateOpening() > $against->getDateOpening()
                        && $period->getDateOpening() < $against->getDateOpening()) {
                        // the period date opening is inside another opening line
                        return array(
                            'result' => self::ERROR_OPENING_NOT_CLOSE_IS_INSIDE_CLOSED_ACCOMPANYING_PERIOD_LINE,
                            'dateOpening' => $against->getDateOpening(), 
                            'dateClosing' => $against->getDateClosing(),
                            'date' => $period->getDateOpening()
                            );
                     }
                     
                    if ($period->getDateOpening() < $against->getDateOpening()
                        && $period->getDateClosing() > $against->getDateClosing()) {
                        // the period date opening is inside another opening line
                        return array(
                            'result' => self::ERROR_OPENING_IS_BEFORE_OTHER_LINE_AND_CLOSED_IS_AFTER_THIS_LINE,
                            'dateOpening' => $against->getDateOpening(), 
                            'dateClosing' => $against->getDateClosing(),
                            'date' => $period->getDateOpening()
                            );
                    }
                     
                    //if we have an aopening later...
                    if ($period->getDateOpening() < $against->getDateClosing()) {
                        return array( 'result' => self::ERROR_OPENING_NOT_CLOSED_IS_BEFORE_NEW_LINE,
                            'dateOpening' => $against->getDateOpening(), 
                            'dateClosing' => $against->getDateClosing(),
                            'date' => $period->getDateOpening()
                        );
                    }
                }
            } else {
                //we must check there is not covering lines
                 
                foreach ($periods as $subKey => $against) {
                    //check if dateOpening is inside an `against` line
                    if ($period->getDateOpening() > $against->getDateOpening()
                        && $period->getDateOpening() < $against->getDateClosing()) {
                            return array(
                                'result' => self::ERROR_OPENING_IS_INSIDE_CLOSING,
                                'dateOpening' => $against->getDateOpening(), 
                                'dateClosing' => $against->getDateClosing(),
                                'date' => $period->getDateOpening()
                                );
                    }
                }
            }
        }
        return true;
    }
}