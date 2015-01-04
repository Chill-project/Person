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

/**
 * Load people into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 * @author Marc Ducobu <marc@champs-libres.coop>
 */
class LoadPeople extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{    
    
    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;
    
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
            
            $sex = $this->genres[array_rand($this->genres)];
            
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
            
            if ($sex === Person::GENRE_MAN) {
                $firstName = $this->firstNamesMale[array_rand($this->firstNamesMale)];
            } else {
                $firstName = $this->firstNamesFemale[array_rand($this->firstNamesFemale)];
            }
            
            $person = array(
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'Genre' => $sex,
                'Nationality' => (rand(0,100) > 50) ? NULL: 'BE'
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
                'DateOfBirth' => "1960-10-12",
                'PlaceOfBirth' => "Ottignies Louvain-La-Neuve",
                'Genre' => Person::GENRE_MAN,
                'Email' => "Email d'un ami: roger@tt.com",
                'CountryOfBirth' => 'BE',
                'Nationality' => 'BE',
                'CFData' => array()
            ), $specific);
    }
    
    private function addAPerson(array $person, ObjectManager $manager)
    {
        $p = new Person();
            
        foreach ($person as $key => $value) {
            switch ($key) {
                case 'CountryOfBirth':
                    $p->setCountryOfBirth($this->getCountry($value));
                    break;
                case 'Nationality':
                    $p->setNationality($this->getCountry($value));
                    break;
                case 'DateOfBirth':
                    $value = new \DateTime($value);


                default:
                    call_user_func(array($p, 'set'.$key), $value);
            }
        }

        $manager->persist($p);
        echo "add person'".$p->__toString()."'\n";
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
    
    private $genres = array(Person::GENRE_MAN, Person::GENRE_WOMAN);  
    
    private $years = array();
    
    private $month = array();
    
    private $day = array();
    
    private $peoples = array(
        array(
            'FirstName' => "Depardieu",
            'LastName' => "Gérard",
            'DateOfBirth' => "1948-12-27",
            'PlaceOfBirth' => "Châteauroux",
            'Genre' => Person::GENRE_MAN,
            'CountryOfBirth' => 'FR',
            'Nationality' => 'RU'      
        ),
       array(
          //to have a person with same firstname as Gérard Depardieu
            'FirstName' => "Depardieu",
            'LastName' => "Jean",
            'DateOfBirth' => "1960-10-12",
            'CountryOfBirth' => 'FR',
            'Nationality' => 'FR'      
        ),
       array(
          //to have a person with same birthdate of Gérard Depardieu
          'FirstName' => 'Van Snick',
          'LastName' => 'Bart',
          'DateOfBirth' => '1948-12-27'
       ),
       array(
          //to have a woman with Depardieu as FirstName
          'FirstName' => 'Depardieu',
          'LastName' => 'Charline',
          'Genre' => Person::GENRE_WOMAN
       ),
       array(
          //to have a special character in lastName
          'FirstName' => 'Manço',
          'LastName' => 'Étienne'
       )
    );
}
