<?php

/*
 * Copyright (C) 2015 Julien Fastré <julien.fastre@champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\PersonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Chill\PersonBundle\Entity\Person;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PersonControllerViewTest extends WebTestCase
{
    
    /**
     *
     * @var \Doctrine\ORM\EntityManagerInterface 
     */
    private $em;
    
    /**
     *
     * @var Person
     */
    private $person;
    
    public function setUp()
    {
        static::bootKernel();
        
        $this->em = static::$kernel->getContainer()
              ->get('doctrine.orm.entity_manager');
        
        $center = $this->em->getRepository('ChillMainBundle:Center')
              ->findOneBy(array('name' => 'Center A'));
       
        $this->person = (new Person())
                ->setLastName("Tested Person")
                ->setFirstName("Réginald")
                ->setCenter($center)
                ->setGenre(Person::GENRE_MAN);
        
        $this->em->persist($this->person);
        $this->em->flush();
        
        $this->seeUrl  = '/en/person/'.$this->person->getId().'/general';
    }
    
    public function testViewPerson()
    {
        $client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center a_social',
           'PHP_AUTH_PW'   => 'password',
        ));
        
        $client->request('GET', $this->seeUrl);
        
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
    
    public function testViewPersonAccessDeniedForUnauthorized()
    {
        $client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center b_social',
           'PHP_AUTH_PW'   => 'password',
        ));
        
        $client->request('GET', $this->seeUrl);
        
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
    
    protected function refreshPerson() 
    {
        $this->person = $this->em->getRepository('ChillPersonBundle:Person')
              ->find($this->person->getId());
    }
    
    public function tearDown()
    {
        $this->refreshPerson();
        $this->em->remove($this->person);
        $this->em->flush();
    }
    
}
