<?php

namespace CL\Chill\PersonBundle\DataFixtures\ORM;

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
    
    public function prepare() {

        
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
    
    public function getOrder() {
        return 1302;
    }
    
    
    
    public function load(ObjectManager $manager) {
        
        echo "loading people...\n";
        
        $this->prepare();
        
        $choose_name_or_tri = array('tri', 'tri', 'name', 'tri');
        
        $i = 0;
        
        do {
            
            echo "add a person...";
            $i++;
            
            if ($choose_name_or_tri[array_rand($choose_name_or_tri)] === 'tri' ) {
                $length = rand(2, 3);
                $name = '';
                for ($j = 0; $j <= $length; $j++) {
                    $name .= $this->names_trigrams[array_rand($this->names_trigrams)];
                }
                $name = ucfirst($name);
                
            } else {
                $name = $this->names[array_rand($this->names)];
            }
            
            $person = array(
                'Name' => $name,
                'Surname' => $this->surnames[array_rand($this->surnames)],
                'DateOfBirth' => "1960-10-12",
                'PlaceOfBirth' => "Ottignies Louvain-La-Neuve",
                'Genre' => Person::GENRE_MAN,
                'CivilUnion' => Person::CIVIL_DIVORCED,
                'NbOfChild' => 0,
                'BelgianNationalNumber' => '12-10-16-269-24',
                'Email' => "Email d'un ami: roger@tt.com",
                'CountryOfBirth' => 'France',
                'Nationality' => 'Russie'
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
            var_dump($p);
            $manager->persist($p);
        } while ($i <= 100);
        
        $manager->flush();
    }
    
    
    private $surnames = array("Jean", "Mohamed", "Alfred", "Robert", "Svedana", "Sevlatina",
        "Irène", "Marcelle", "Compère", "Jean-de-Dieu", "Corentine", "Alfonsine",
        "Caroline", "Charles", "Pierre", "Luc", "Mathieu", "Alain", "Etienne", "Eric",
        "Solange", "Corentin", "Gaston", "Spirou", "Fantasio", "Mahmadou", "Mohamidou",
        "Vursuv", "Gostine");
    
    private $names = array("Diallo", "Bah", "Gaillot");
    private $names_trigrams = array("fas", "tré", "hu", 'blart', 'van', 'der', 'lin', 'den',
        'ta', 'mi', 'gna', 'bol', 'sac', 'ré', 'jo', 'du', 'pont', 'cas', 'tor', 'rob', 'al',
        'ma', 'gone', 'car');
    
    private $genres = array(Person::GENRE_MAN, Person::GENRE_WOMAN);
    
    private $CivilUnions = array(Person::CIVIL_COHAB, Person::CIVIL_DIVORCED, 
        Person::CIVIL_SEPARATED, Person::CIVIL_SINGLE, Person::CIVIL_UNKNOW,
            Person::CIVIL_WIDOW);
    
    private $NbOfChild = array(0, 0, 1, 1, 1, 1, 1, 2, 2, 3, 4, 5, 6);
    
    private $years = array();
    
    private $month = array();
    
    private $day = array();
    
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
