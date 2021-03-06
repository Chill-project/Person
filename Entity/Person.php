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
use Chill\PersonBundle\Entity\MaritalStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Chill\MainBundle\Entity\HasCenterInterface;
use Chill\MainBundle\Entity\Address;
use Doctrine\Common\Collections\Criteria;

/**
 * Person
 */
class Person implements HasCenterInterface {
    /** @var integer  The person's id */
    private $id;

    /** @var string The person's first name */
    private $firstName;

    /** @var string The person's last name */
    private $lastName;

    /** @var \DateTime The person's birthdate */
    private $birthdate; //to change in birthdate

    /** @var string The person's place of birth */
    private $placeOfBirth = '';
    
    /** @var \Chill\MainBundle\Entity\Country The person's country of birth */
    private $countryOfBirth;

    /** @var \Chill\MainBundle\Entity\Country The person's nationality */
    private $nationality;  

    /** @var string The person's gender */
    private $gender;
    
    const MALE_GENDER = 'man'; 
    const FEMALE_GENDER = 'woman';

    /** @var \Chill\PersonBundle\Entity\MaritalStatus The marital status of the person */
    private $maritalStatus;

    //TO-ADD : address

    /** @var string The person's email */
    private $email = '';

    /** @var string The person's phonenumber */
    private $phonenumber = '';

    //TO-ADD : caseOpeningDate

    //TO-ADD nativeLanguag

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection The person's spoken 
     * languages (ArrayCollection of Languages)
     */
    private $spokenLanguages;
    
    /** @var \Chill\MainBundle\Entity\Center The person's center */
    private $center;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection The person's 
     * accompanying periods (when the person was accompanied by the center)*/
    private $accompanyingPeriods; //TO-CHANGE in accompanyingHistory

    /** @var string A remark over the person */
    private $memo = ''; // TO-CHANGE in remark
    
    /**
     * @var boolean
     */
    private $proxyAccompanyingPeriodOpenState = false; //TO-DELETE ?


    /** @var array Array where customfield's data are stored */
    private $cFData;
    
    /**
     *
     * @var \Doctrine\Common\Collections\Collection
     */
    private $addresses;
    
    public function __construct(\DateTime $opening = null) {
        $this->accompanyingPeriods = new ArrayCollection();
        $this->spokenLanguages = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        
        if ($opening === null) {
            $opening = new \DateTime();
        }
        
        $this->open(new AccompanyingPeriod($opening));
    }
    
    /**
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
    public function close(AccompanyingPeriod $accompanyingPeriod = null) 
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
     * Get the accompanying periods of a give person with the 
     * chronological order.
     *
     * @return AccompanyingPeriod[]
     */
    public function getAccompanyingPeriodsOrdered() {
        $periods = $this->getAccompanyingPeriods()->toArray();
        
        //order by date :
        usort($periods, function($a, $b) {
            $dateA = $a->getOpeningDate();
            $dateB = $b->getOpeningDate();
            
            if ($dateA == $dateB) {
                $dateEA = $a->getClosingDate();
                $dateEB = $b->getClosingDate();
                
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
     * Set birthdate
     *
     * @param \DateTime $birthdate
     * @return Person
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    
        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime 
     */
    public function getBirthdate()
    {
        return $this->birthdate;
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
     * Set gender
     *
     * @param string $gender
     * @return Person
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    
        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }
    
    /**
     * return gender as a Numeric form.
     * This is used for translations
     * @return int
     */
    public function getGenderNumeric() {
        if ($this->getGender() == self::FEMALE_GENDER) {
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
     * Set maritalStatus
     *
     * @param \Chill\PersonBundle\Entity\MaritalStatus $maritalStatus
     * @return Person
     */
    public function setMaritalStatus(MaritalStatus $maritalStatus = null)
    {
        $this->maritalStatus = $maritalStatus;
        return $this;
    }

    /**
     * Get maritalStatus
     *
     * @return \Chill\PersonBundle\Entity\MaritalStatus 
     */
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
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
        if ($this->cFData === null) {
            $this->cFData = [];
        }
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
    
    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;
        
        return $this;
    }
    
    public function removeAddress(Address $address)
    {
        $this->addresses->removeElement($address);
    }
    
    /**
     * By default, the addresses are ordered by date, descending (the most
     * recent first)
     * 
     * @return \Chill\MainBundle\Entity\Address[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }
    
    public function getLastAddress(\DateTime $date = null)
    {
        if ($date === null) {
            $date = new \DateTime('now');
        }
        
        $addresses = $this->getAddresses();
        
        if ($addresses == null) {
            
            return null;
        }
        
        return $addresses->first();
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
                    'dateOpening' => $periodAfterI->getOpeningDate(),
                    'dateClosing' => $periodAfterI->getClosingDate(),
                    'date' => $periodI->getOpeningDate()
                );
            } elseif ($periodI->getClosingDate() >= $periodAfterI->getOpeningDate()) {
                return array(
                    'result' => self::ERROR_PERIODS_ARE_COLLAPSING,
                    'dateOpening' => $periodI->getOpeningDate(),
                    
                    'dateClosing' => $periodI->getClosingDate(),
                    'date' => $periodAfterI->getOpeningDate()
                );
            }
            $i++;
        }
        
        return true;
    }
}