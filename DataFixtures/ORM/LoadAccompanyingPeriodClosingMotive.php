<?php

namespace Chill\PersonBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Chill\PersonBundle\Entity\AccompanyingPeriod\ClosingMotive;

/**
 * Load closing motives into database
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadAccompanyingPeriodClosingMotive extends AbstractFixture 
    implements OrderedFixtureInterface 
{
    
    public function getOrder() {
        return 9500;
    }
    
    public static $closingMotives = array(
        'nothing_to_do' => array(
           'name' => array(
              'fr' => 'Plus rien à faire',
              'en' => 'Nothing to do',
              'nl' => 'nieks meer te doen'
           )
        ),
        'did_not_come_back' => array(
           'name' => array(
              'fr' => "N'est plus revenu",
              'en' => "Did'nt come back",
              'nl' => "Niet teruggekomen"
           )
        ),
       'no_more_money' => array(
          'active' => false,
          'name' => array(
             'fr' => "Plus d'argent",
             'en' => "No more money",
             'nl' => "Geen geld"
          )
       )
    );
    
    public static $references = array();
    
    public function load(ObjectManager $manager) 
    {
        foreach (static::$closingMotives as $ref => $new) {
            $motive = new ClosingMotive();
            $motive->setName($new['name'])
                  ->setActive((isset($new['active']) ? $new['active'] : true))
                ;
            
            $manager->persist($motive);
            $this->addReference($ref, $motive);
            echo "Adding ClosingMotive $ref\n";
        }
        
        $manager->flush();
    }
        
        


}
