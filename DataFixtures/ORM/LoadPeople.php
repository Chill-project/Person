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

/**
 * Load people into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 * @author Marc Ducobu <marc@champs-libres.coop>
 */
class LoadPeople extends AbstractFixture implements OrderedFixtureInterface
{    
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
        echo "loading people...\n";
        
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
                'DateOfBirth' => "1960-10-12",
                'PlaceOfBirth' => "Ottignies Louvain-La-Neuve",
                'Genre' => $sex,
                'Email' => "Email d'un ami: roger@tt.com",
                'CountryOfBirth' => 'France',
                'Nationality' => 'Russie',
                'CFData' => array()
            );
            
            $p = new Person();
            
            foreach ($person as $key => $value) {
                switch ($key) {
                    case 'CountryOfBirth':
                        break;
                    case 'Nationality':
                        break;
                    case 'DateOfBirth':
                        $value = new \DateTime($value);
                    
                    
                    default:
                        call_user_func(array($p, 'set'.$key), $value);
                }
            }

            $manager->persist($p);
            echo "add person'".$p->__toString()."'\n";
            
        } while ($i <= 100);
        
        $manager->flush();
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
            'LastName' => "Jean",
            'DateOfBirth' => "1960-10-12",
            'PlaceOfBirth' => "Ottignies Louvain-La-Neuve",
            'Genre' => Person::GENRE_MAN,
            'Email' => "Email d'un ami: roger@tt.com",
            'CountryOfBirth' => 'France',
            'Nationality' => 'Russie'      
        )
    );
}
