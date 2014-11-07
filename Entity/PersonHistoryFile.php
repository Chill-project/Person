<?php

namespace Chill\PersonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * PersonHistoryFile
 */
class PersonHistoryFile
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date_opening;

    /**
     * @var \DateTime
     */
    private $date_closing;

    /**
     * @var string
     */
    private $motive = '';

    /**
     * @var string
     */
    private $memo = '';

    /**
     * @var \Chill\PersonBundle\Entity\Person
     */
    private $person;
    
    /**
     * 
     * @param \DateTime $dateOpening
     * @uses PersonHistoryFile::setDateClosing()
     */
    public function __construct(\DateTime $dateOpening) {
        $this->setDateOpening($dateOpening);
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
     * Set date_opening
     *
     * @param \DateTime $dateOpening
     * @return PersonHistoryFile
     */
    public function setDateOpening($dateOpening)
    {
        $this->date_opening = $dateOpening;
    
        return $this;
    }

    /**
     * Get date_opening
     *
     * @return \DateTime 
     */
    public function getDateOpening()
    {
        return $this->date_opening;
    }

    /**
     * Set date_closing
     * 
     * For closing a Person file, you should use Person::setClosed instead.
     *
     * @param \DateTime $dateClosing
     * @return PersonHistoryFile
     * 
     */
    public function setDateClosing($dateClosing)
    {
        $this->date_closing = $dateClosing;
    
        return $this;
    }

    /**
     * Get date_closing
     *
     * @return \DateTime 
     */
    public function getDateClosing()
    {
        return $this->date_closing;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isOpen() {
        if ($this->getDateClosing() === null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set motive
     *
     * @param string $motive
     * @return PersonHistoryFile
     */
    public function setMotive($motive)
    {
        $this->motive = $motive;
    
        return $this;
    }

    /**
     * Get motive
     *
     * @return string 
     */
    public function getMotive()
    {
        return $this->motive;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return PersonHistoryFile
     */
    public function setMemo($memo)
    {
        if ($memo === null) {
            $memo = '';
        }
        
        $this->memo = $memo;
    
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
     * Set person.
     * 
     * For consistency, you should use Person::addHistoryFile instead.
     *
     * @param \Chill\PersonBundle\Entity\Person $person
     * @return PersonHistoryFile
     * @see Person::addHistoryFile
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
    
    
    /// VALIDATION function
    
    
    public function isDateConsistent(ExecutionContextInterface $context) {
        if ($this->isOpen()) {
            return;
        }
        
        if ($this->isClosingAfterOpening() === false) {
            $context->addViolationAt('dateClosing', 
                    'validation.PersonHistoryFile.constraint.dateOfClosing_before_dateOfOpening',
                    array(), null);
        }
    }
    
    /**
     * 
     * @return boolean
     */
    public function isClosingAfterOpening() {
        
        $diff = $this->getDateOpening()->diff($this->getDateClosing());
        
        if ($diff->invert === 0) {
            return true;
        } else {
            return false;
        }

    }
}
