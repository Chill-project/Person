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
