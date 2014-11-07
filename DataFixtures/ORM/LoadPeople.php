<?php

namespace Chill\PersonBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Chill\PersonBundle\Entity\Person;

/**
 * Load people into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadPeople extends AbstractFixture implements OrderedFixtureInterface {
    
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
        return 10000;
    }
    
    
    
    public function load(ObjectManager $manager) {
        
        echo "loading people...\n";
        
        $this->prepare();
        
        $choose_name_or_tri = array('tri', 'tri', 'name', 'tri');
        
        $i = 0;
        
        do {
            
            
            $i++;
            
            $sex = $this->genres[array_rand($this->genres)];
            
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
            
            if ($sex === Person::GENRE_MAN) {
                $surname = $this->surnames_male[array_rand($this->surnames_male)];
            } else {
                $surname = $this->surnames_female[array_rand($this->surnames_female)];
            }
            
            
            $person = array(
                'Name' => $name,
                'Surname' => $surname,
                'DateOfBirth' => "1960-10-12",
                'PlaceOfBirth' => "Ottignies Louvain-La-Neuve",
                'Genre' => $sex,
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

            $manager->persist($p);
            echo "add person'".$p->__toString()."'\n";
            
        } while ($i <= 100);
        
        $manager->flush();
    }
    
    
    private $surnames_male = array("Jean", "Mohamed", "Alfred", "Robert", 
         "Compère", "Jean-de-Dieu", 
         "Charles", "Pierre", "Luc", "Mathieu", "Alain", "Etienne", "Eric",
         "Corentin", "Gaston", "Spirou", "Fantasio", "Mahmadou", "Mohamidou",
        "Vursuv" );
    private $surnames_female = array("Svedana", "Sevlatina","Irène", "Marcelle",
        "Corentine", "Alfonsine","Caroline","Solange","Gostine", "Fatoumata",
        "Groseille", "Chana", "Oxana", "Ivana");
    
    private $names = array("Diallo", "Bah", "Gaillot");
    private $names_trigrams = array("fas", "tré", "hu", 'blart', 'van', 'der', 'lin', 'den',
        'ta', 'mi', 'gna', 'bol', 'sac', 'ré', 'jo', 'du', 'pont', 'cas', 'tor', 'rob', 'al',
        'ma', 'gone', 'car',"fu", "ka", "lot", "no", "va", "du", "bu", "su",
        "lo", 'to', "cho", "car", 'mo','zu', 'qi', 'mu');
    
    private $genres = array(Person::GENRE_MAN, Person::GENRE_WOMAN);
    
    
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
            'Email' => "Email d'un ami: roger@tt.com",
            'CountryOfBirth' => 'France',
            'Nationality' => 'Russie'
            
            
        )
    );


}
