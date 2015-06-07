<?php

namespace Chill\PersonBundle\Entity;

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>, <info@champs-libres.coop>
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
use Chill\MainBundle\Entity\HasCenterInterface;

/**
 * Person
 */
class Person implements HasCenterInterface {
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
    
    /**
     *
     * @var \Chill\MainBundle\Entity\Center
     */
    private $center;
    
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
    private $accompanyingPeriods;
    
    /**
     *
     * @var boolean
     */
    private $proxyAccompanyingPeriodOpenState = false;


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
        $this->accompanyingPeriods = new ArrayCollection();
        $this->spokenLanguages = new ArrayCollection();
        
        if ($opening === null) {
            $opening = new \DateTime();
        }
        
        $this->open(new AccompanyingPeriod($opening));
    }
    
    /**
     * 
     * @param \Chill\PersonBundle\Entity\AccompanyingPeriod $accompanyingPeriod
     * @uses AccompanyingPeriod::setPerson
     */
    public function addAccompanyingPeriod(AccompanyingPeriod $accompanyingPeriod) {
        $accompanyingPeriod->setPerson($this);
        $this->accompanyingPeriods->add($accompanyingPeriod);
    }
    
    public function removeAccompanyingPeriod(AccompanyingPeriod $accompanyingPeriod) {
        $this->accompanyingPeriods->remove($accompanyingPeriod);
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
        $this->proxyAccompanyingPeriodOpenState = true;
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
     * @param accompanyingPeriod
     * @throws \Exception if two lines of the accompanying period are open.
     */
    public function close(AccompanyingPeriod $accompanyingPeriod) 
    {
        $this->proxyAccompanyingPeriodOpenState = false;
    }
    
    /**
     * 
     * @return null|AccompanyingPeriod
     */
    public function getCurrentAccompanyingPeriod() {
        if ($this->proxyAccompanyingPeriodOpenState === false) {
            return null;
        }
        
        foreach ($this->accompanyingPeriods as $period) {
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
        return $this->accompanyingPeriods;
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
        return $this->proxyAccompanyingPeriodOpenState;
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
     * Get center
     * 
     * @return \Chill\MainBundle\Entity\Center
     */
    public function getCenter()
    {
        return $this->center;
    }

    /**
     * Set the center
     * 
     * @param \Chill\MainBundle\Entity\Center $center
     * @return \Chill\PersonBundle\Entity\Person
     */
    public function setCenter(\Chill\MainBundle\Entity\Center $center)
    {
        $this->center = $center;
        return $this;
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
    
    /**
     * Validation callback that checks if the accompanying periods are valid
     * 
     * This method add violation errors.
     */
    public function isAccompanyingPeriodValid(ExecutionContextInterface $context)
    {
        $r = $this->checkAccompanyingPeriodsAreNotCollapsing();
        
        if ($r !== true) {
            if ($r['result'] === self::ERROR_PERIODS_ARE_COLLAPSING) {
                $context->addViolationAt('accompanyingPeriods',
                    'Two accompanying periods have days in commun',
                    array());
            }
            
            if ($r['result'] === self::ERROR_ADDIND_PERIOD_AFTER_AN_OPEN_PERIOD) {
                $context->addViolationAt('accompanyingPeriods',
                    'A period is opened and a period is added after it',
                    array());
            }
        }
    }
    
    const ERROR_PERIODS_ARE_COLLAPSING = 1; // when two different periods
    // have days in commun
    const ERROR_ADDIND_PERIOD_AFTER_AN_OPEN_PERIOD = 2; // where there exist
    // a period opened and another one after it
    
    /**
     * Function used for validation that check if the accompanying periods of
     * the person are not collapsing (i.e. have not shared days) or having
     * a period after an open period.
     * 
     * @return true | array True if the accompanying periods are not collapsing,
     * an array with data for displaying the error
     */
    public function checkAccompanyingPeriodsAreNotCollapsing()
    {
        $periods = $this->getAccompanyingPeriodsOrdered();
        $periodsNbr = sizeof($periods);
        $i = 0;
        
        while($i < $periodsNbr - 1) {
            $periodI = $periods[$i];
            $periodAfterI = $periods[$i + 1];
            
            if($periodI->isOpen()) {                
                return array(
                    'result' => self::ERROR_ADDIND_PERIOD_AFTER_AN_OPEN_PERIOD,
                    'dateOpening' => $periodAfterI->getDateOpening(),
                    'dateClosing' => $periodAfterI->getDateClosing(),
                    'date' => $periodI->getDateOpening()
                );
            } elseif ($periodI->getDateClosing() >= $periodAfterI->getDateOpening()) {
                return array(
                    'result' => self::ERROR_PERIODS_ARE_COLLAPSING,
                    'dateOpening' => $periodI->getDateOpening(),
                    
                    'dateClosing' => $periodI->getDateClosing(),
                    'date' => $periodAfterI->getDateOpening()
                );
            }
            $i++;
        }
        
        return true;
    }
}