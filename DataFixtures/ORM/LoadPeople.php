<?php

namespace CL\Chill\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CL\Chill\PersonBundle\Entity\Person;

/**
 * Load people into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadPeople extends AbstractFixture {
    
    public function getOrder() {
        return 1001;
    }
    
    
    
    public function load(ObjectManager $manager) {
        foreach ($this->peoples as $person) {
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
            var_dump($p);
            $manager->persist($p);
        }
        
        $manager->flush();
    }
    
    private $peoples = array(
        array(
            'Name' => "Depardieu",
            'Surname' => "Jean",
            'DateOfBirth' => "1960-10-12",
            'PlaceOfBirth' => "Ottignies Louvain-La-Neuve",
            'Genre' => Person::GENRE_MAN,
            'CivilUnion' => Person::CIVIL_DIVORCED,
            'NbOfChild' => 0,
            'BelgianNationalNumber' => '12-10-16-269-24',
            'Email' => "Email d'un ami: roger@tt.com",
            'CountryOfBirth' => 'France',
            'Nationality' => 'Russie'
            
            
        )
    );


}
