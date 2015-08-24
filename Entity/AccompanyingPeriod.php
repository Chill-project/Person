<?php

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

namespace Chill\PersonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * AccompanyingPeriod
 */
class AccompanyingPeriod
{
    /** @var integer */
    private $id;

    /** @var \DateTime */
    private $openingDate;

    /** @var \DateTime */
    private $closingDate;

    /** @var string */
    private $remark = '';

    /** @var \Chill\PersonBundle\Entity\Person */
    private $person;
    
    /** @var AccompanyingPeriod\ClosingMotive */
    private $closingMotive = null;
    
    /**
     * 
     * @param \DateTime $dateOpening
     * @uses AccompanyingPeriod::setClosingDate()
     */
    public function __construct(\DateTime $dateOpening) {
        $this->setOpeningDate($dateOpening);
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
     * Set openingDate
     *
     * @param \DateTime $dateOpening
     * @return AccompanyingPeriod
     */
    public function setOpeningDate($openingDate)
    {
        $this->openingDate = $openingDate;
    
        return $this;
    }

    /**
     * Get openingDate
     *
     * @return \DateTime 
     */
    public function getOpeningDate()
    {
        return $this->openingDate;
    }

    /**
     * Set closingDate
     * 
     * For closing a Person file, you should use Person::setClosed instead.
     *
     * @param \DateTime $dateClosing
     * @return AccompanyingPeriod
     * 
     */
    public function setClosingDate($closingDate)
    {
        $this->closingDate = $closingDate;
    
        return $this;
    }

    /**
     * Get closingDate
     *
     * @return \DateTime 
     */
    public function getClosingDate()
    {
        return $this->closingDate;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isOpen() {
        if ($this->getClosingDate() === null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set remark
     *
     * @param string $remark
     * @return AccompanyingPeriod
     */
    public function setRemark($remark)
    {
        if ($remark === null) {
            $remark = '';
        }
        
        $this->remark = $remark;
    
        return $this;
    }

    /**
     * Get remark
     *
     * @return string 
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * Set person.
     * 
     * For consistency, you should use Person::addAccompanyingPeriod instead.
     *
     * @param \Chill\PersonBundle\Entity\Person $person
     * @return AccompanyingPeriod
     * @see Person::addAccompanyingPeriod
     */
    public function setPerson(\Chill\PersonBundle\Entity\Person $person = null)
    {
        $this->person = $person;
    
        return $this;
    }

    /**
     * Get person
     *
     * @return \Chill\PersonBundle\Entity\Person 
     */
    public function getPerson()
    {
        return $this->person;
    }
    
    public function getClosingMotive()
    {
        return $this->closingMotive;
    }

    public function setClosingMotive(AccompanyingPeriod\ClosingMotive $closingMotive)
    {
        $this->closingMotive = $closingMotive;
        return $this;
    }

    /// VALIDATION function
    public function isDateConsistent(ExecutionContextInterface $context) {
        if ($this->isOpen()) {
            return;
        }
        
        if (! $this->isClosingAfterOpening()) {
            $context->addViolationAt('dateClosing', 
                'validation.AccompanyingPeriod.constraint.dateOfClosing_before_dateOfOpening',
                array(), null);
        }
    }
    
    /**
     * Returns true if the closing date is after the opening date.
     * 
     * @return boolean
     */
    public function isClosingAfterOpening() {
        $diff = $this->getOpeningDate()->diff($this->getClosingDate());
        
        if ($diff->invert === 0) {
            return true;
        } else {
            return false;
        }
    }
}
