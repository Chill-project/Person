<?php

namespace CL\Chill\PersonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 */
class Person
{
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
    private $civil_union;
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $countryOfBirth;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $nationality;    

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
     * Set civil_union
     *
     * @param string $civilUnion
     * @return Person
     */
    public function setCivilUnion($civilUnion)
    {
        $this->civil_union = $civilUnion;
    
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
     * Set address
     *
     * @param string $address
     * @return Person
     */
    public function setAddress($address)
    {
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
        $this->countryOfBirth = $countryOfBirth;
    
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
}