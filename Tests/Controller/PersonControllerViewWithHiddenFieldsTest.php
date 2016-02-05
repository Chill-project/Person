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
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Marc Ducobu <marc.ducobu@champs-libres.coop>
 */
class PersonControllerViewTestWithHiddenFields extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManagerInterface The entity manager */
    private $em;
    
    /** @var Person A person used on which to run the test */
    private $person;

    /** @var String The url to view the person details */
    private $viewUrl;
    
    public function setUp()
    {
        static::bootKernel(array('environment' => 'test_with_hidden_fields'));
        
        $this->em = static::$kernel->getContainer()
            ->get('doctrine.orm.entity_manager');
        
        $center = $this->em->getRepository('ChillMainBundle:Center')
            ->findOneBy(array('name' => 'Center A'));
       
        $this->person = (new Person())
            ->setLastName("Tested Person")
            ->setFirstName("Réginald")
            ->setCenter($center)
            ->setGender(Person::MALE_GENDER);
        
        $this->em->persist($this->person);
        $this->em->flush();
        
        $this->viewUrl  = '/en/person/'.$this->person->getId().'/general';
    }
    
    /**
     * Test if the  view page is accessible
     * 
     * @group configurable_fields
     */
    public function testViewPerson()
    {
        $client = static::createClient(
                array('environment' => 'test_with_hidden_fields'), 
                array(
                    'PHP_AUTH_USER' => 'center a_social',
                    'PHP_AUTH_PW'   => 'password',
                    'HTTP_ACCEPT_LANGUAGE' => 'fr'
                )
            );
        
        $crawler = $client->request('GET', $this->viewUrl);
        $response = $client->getResponse();
        
        $this->assertTrue($response->isSuccessful());

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Tested Person")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Réginald")')->count());
        $this->assertNotContains('Email addresses', $crawler->text());
        $this->assertNotContains('Phonenumber', $crawler->text());
        $this->assertNotContains('Langues parlées', $crawler->text());
        $this->assertNotContains(/* Etat */ 'civil', $crawler->text());
    }
    
    /**
     * Reload the person from the db
     */
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
