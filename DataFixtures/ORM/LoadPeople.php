<?php

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

namespace Chill\PersonBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Chill\PersonBundle\Entity\Person;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Chill\MainBundle\DataFixtures\ORM\LoadPostalCodes;
use Chill\MainBundle\Entity\Address;

/**
 * Load people into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 * @author Marc Ducobu <marc@champs-libres.coop>
 */
class LoadPeople extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{    
    
    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;
    
    protected $faker;
    
    public function __construct()
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }
    
    public function prepare()
    {
        //prepare days, month, years
        $y = 1950;
        do {
            $this->years[] = $y;
            $y = $y +1;
        } while ($y >= 1990);
        
        $m = 1;
        do {
            $this->month[] = $m;
            $m = $m +1;
        } while ($m >= 12);
        
        $d = 1;
        do {
            $this->day[] = $d;
            $d = $d + 1;
        } while ($d <= 28);
    }
    
    public function getOrder()
    {
        return 10000;
    }
    
    public function load(ObjectManager $manager)
    {
        $this->loadRandPeople($manager);
        $this->loadExpectedPeople($manager);
        
        $manager->flush();
    }
    
    public function loadExpectedPeople(ObjectManager $manager)
    {
        echo "loading expected people...\n";
        
        foreach ($this->peoples as $person) {
            $this->addAPerson($this->fillWithDefault($person), $manager);
        }
    }
    
    public function loadRandPeople(ObjectManager $manager)
    {
        echo "loading rand people...\n";
        
        $this->prepare();
        
        $chooseLastNameOrTri = array('tri', 'tri', 'name', 'tri');
        
        $i = 0;
        
        do {
            $i++;
            
            $sex = $this->genders[array_rand($this->genders)];
            
            if ($chooseLastNameOrTri[array_rand($chooseLastNameOrTri)] === 'tri' ) {
                $length = rand(2, 3);
                $lastName = '';
                for ($j = 0; $j <= $length; $j++) {
                    $lastName .= $this->lastNamesTrigrams[array_rand($this->lastNamesTrigrams)];
                }
                $lastName = ucfirst($lastName);
            } else {
                $lastName = $this->lastNames[array_rand($this->lastNames)];
            }
            
            if ($sex === Person::MALE_GENDER) {
                $firstName = $this->firstNamesMale[array_rand($this->firstNamesMale)];
            } else {
                $firstName = $this->firstNamesFemale[array_rand($this->firstNamesFemale)];
            }
            
            // add an address on 80% of the created people
            if (rand(0,100) < 80) {
                $address = $this->getRandomAddress();
                // on 30% of those person, add multiple addresses
                if (rand(0,10) < 4) {
                    $address = array(
                       $address,
                       $this->getRandomAddress()
                    );
                }
            } else {
                $address = null;
            }
            
            $person = array(
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'Gender' => $sex,
                'Nationality' => (rand(0,100) > 50) ? NULL: 'BE',
                'center' => (rand(0,1) == 0) ? 'centerA': 'centerB',
                'Address' => $address,
                'maritalStatus' => $this->maritalStatusRef[array_rand($this->maritalStatusRef)]
            );
            
            $this->addAPerson($this->fillWithDefault($person), $manager);
            
        } while ($i <= 100);
    }
    
    /**
     * fill a person array with default value
     * 
     * @param string[] $specific
     */
    private function fillWithDefault(array $specific)
    {
        return array_merge(array(
                'Birthdate' => "1960-10-12",
                'PlaceOfBirth' => "Ottignies Louvain-La-Neuve",
                'Gender' => Person::MALE_GENDER,
                'Email' => "Email d'un ami: roger@tt.com",
                'CountryOfBirth' => 'BE',
                'Nationality' => 'BE',
                'CFData' => array(),
                'Address' => null
            ), $specific);
    }
    
    /**
     * create a new person from array data
     * 
     * @param array $person
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function addAPerson(array $person, ObjectManager $manager)
    {
        $p = new Person();

        foreach ($person as $key => $value) {
            switch ($key) {
                case 'CountryOfBirth':
                case 'Nationality':
                    $value = $this->getCountry($value);
                    break;
                case 'Birthdate':
                    $value = new \DateTime($value);
                    break;
                case 'center':
                case 'maritalStatus':
                    $value = $this->getReference($value);
                    break;
            }
            
            //try to add the data using the setSomething function,
            // if not possible, fallback to addSomething function
            if (method_exists($p, 'set'.$key)) {
                call_user_func(array($p, 'set'.$key), $value);
            } elseif (method_exists($p, 'add'.$key)) {
                // if we have a "addSomething", we may have multiple items to add
                // so, we set the value in an array if it is not an array, and 
                // will call the function addSomething multiple times
                if (!is_array($value)) {
                    $value = array($value);
                }
                
                foreach($value as $v) {
                    if ($v !== NULL) {
                        call_user_func(array($p, 'add'.$key), $v);
                    }
                }
                
            } 
        }

        $manager->persist($p);
        echo "add person'".$p->__toString()."'\n";
    }
    
    /**
     * Creata a random address
     * 
     * @return Address
     */
    private function getRandomAddress()
    {
        return (new Address())
                      ->setStreetAddress1($this->faker->streetAddress)
                      ->setStreetAddress2(
                            rand(0,9) > 5 ? $this->faker->streetAddress : ''
                            )
                      ->setPostcode($this->getReference(
                                LoadPostalCodes::$refs[array_rand(LoadPostalCodes::$refs)]
                            ))
                      ->setValidFrom($this->faker->dateTimeBetween('-5 years'))
              ;
    }
    
    private function getCountry($countryCode)
    {
        if ($countryCode === NULL) {
            return NULL;
        }
        return $this->container->get('doctrine.orm.entity_manager')
              ->getRepository('ChillMainBundle:Country')
              ->findOneByCountryCode($countryCode);
    }

    private $maritalStatusRef = ['ms_single', 'ms_married', 'ms_widow', 'ms_separat', 
        'ms_divorce', 'ms_legalco', 'ms_unknown'];
    
    private $firstNamesMale = array("Jean", "Mohamed", "Alfred", "Robert",
        "Compère", "Jean-de-Dieu",
        "Charles", "Pierre", "Luc", "Mathieu", "Alain", "Etienne", "Eric",
        "Corentin", "Gaston", "Spirou", "Fantasio", "Mahmadou", "Mohamidou",
        "Vursuv" );
    private $firstNamesFemale = array("Svedana", "Sevlatina","Irène", "Marcelle",
        "Corentine", "Alfonsine","Caroline","Solange","Gostine", "Fatoumata",
        "Groseille", "Chana", "Oxana", "Ivana");
    
    private $lastNames = array("Diallo", "Bah", "Gaillot");
    private $lastNamesTrigrams = array("fas", "tré", "hu", 'blart', 'van', 'der', 'lin', 'den',
        'ta', 'mi', 'gna', 'bol', 'sac', 'ré', 'jo', 'du', 'pont', 'cas', 'tor', 'rob', 'al',
        'ma', 'gone', 'car',"fu", "ka", "lot", "no", "va", "du", "bu", "su",
        "lo", 'to', "cho", "car", 'mo','zu', 'qi', 'mu');
    
    private $genders = array(Person::MALE_GENDER, Person::FEMALE_GENDER);  
    
    private $years = array();
    
    private $month = array();
    
    private $day = array();
    
    private $peoples = array(
        array(
            'FirstName' => "Depardieu",
            'LastName' => "Gérard",
            'Birthdate' => "1948-12-27",
            'PlaceOfBirth' => "Châteauroux",
            'Gender' => Person::MALE_GENDER,
            'CountryOfBirth' => 'FR',
            'Nationality' => 'RU',
            'center' => 'centerA',
            'maritalStatus' => 'ms_divorce'
        ),
       array(
          //to have a person with same firstname as Gérard Depardieu
            'FirstName' => "Depardieu",
            'LastName' => "Jean",
            'Birthdate' => "1960-10-12",
            'CountryOfBirth' => 'FR',
            'Nationality' => 'FR',
            'center' => 'centerA',
            'maritalStatus' => 'ms_divorce'
        ),
       array(
          //to have a person with same birthdate of Gérard Depardieu
          'FirstName' => 'Van Snick',
          'LastName' => 'Bart',
          'Birthdate' => '1948-12-27',
          'center' => 'centerA',
          'maritalStatus' => 'ms_legalco'
       ),
       array(
          //to have a woman with Depardieu as FirstName
          'FirstName' => 'Depardieu',
          'LastName' => 'Charline',
          'Gender' => Person::FEMALE_GENDER,
          'center' => 'centerA',
          'maritalStatus' => 'ms_legalco'
       ),
       array(
          //to have a special character in lastName
          'FirstName' => 'Manço',
          'LastName' => 'Étienne',
          'center' => 'centerA',
          'maritalStatus' => 'ms_unknown'
       )
    );
}
