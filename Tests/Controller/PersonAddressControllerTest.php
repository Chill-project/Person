<?php

/*
 * Copyright (C) 2016 Champs-Libres <info@champs-libres.coop>
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
class PersonAddressControllerTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManagerInterface The entity manager */
    protected $em;
    
    /** @var Person The person on which the test is executed */
    protected static $person;
    
    /**
     *
     * @var \Chill\MainBundle\Entity\PostalCode
     */
    protected $postalCode;
    
    /**
     *
     * @var \Symfony\Component\BrowserKit\Client
     */
    protected $client;
    
    public static function setUpBeforeClass()
    {
        static::bootKernel();
        
        $em = static::$kernel->getContainer()
            ->get('doctrine.orm.entity_manager');
        
        $center = $em->getRepository('ChillMainBundle:Center')
            ->findOneBy(array('name' => 'Center A'));
       
        self::$person = (new Person())
            ->setLastName("Tested person")
            ->setFirstName("Test")
            ->setCenter($center)
            ->setGender(Person::MALE_GENDER);
        
        $em->persist(self::$person);
        $em->flush();
    }
    
    /**
     * Prepare client and create a random person
     */
    public function setUp()
    {
        static::bootKernel();
        
        $this->em = static::$kernel->getContainer()
            ->get('doctrine.orm.entity_manager');
        
        $this->postalCode = $this->em->getRepository('ChillMainBundle:PostalCode')
                ->findOneBy(array('code' => 1000));
        
        $this->client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center a_social',
           'PHP_AUTH_PW'   => 'password',
        ));
    }
    
    public static function tearDownAfter()
    {
        $this->refreshPerson();
        $this->em->remove(self::$person);
        $this->em->flush();
    }
    
    /**
     * Reload the person from the db
     */
    protected function refreshPerson() 
    {
        self::$person = $this->em->getRepository('ChillPersonBundle:Person')
            ->find(self::$person->getId());
    }
    
    public function testEmptyList()
    {
        $crawler = $this->client->request('GET', '/fr/person/'.
                self::$person->getId().'/address/list');
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        
        $this->assertEquals(1, $crawler->filter('td:contains("Pas d\'adresse renseignée")')
                ->count(),
                "assert that a message say 'no address given'");
        
    }
    
    /**
     * @depends testEmptyList
     */
    public function testCreateAddress()
    {
        $crawler = $this->client->request('GET', '/fr/person/'.
                self::$person->getId().'/address/new');
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        
        $form = $crawler->selectButton('Envoi')->form(array(
            'address[streetAddress1]' => 'Rue de la Paix, 50',
            'address[streetAddress2]' =>  $this->postalCode->getId(),
            'address[validFrom]'      => '15-01-2016'
        ));
        
        $this->client->submit($form);
        
        $crawler = $this->client->followRedirect();
        
        $this->assertRegexp('|/fr/person/[0-9]{1,}/address/list|', 
                $this->client->getHistory()->current()->getUri(),
                "assert that the current page is on |/fr/person/[0-9]{1,}/address/list|");
        $this->assertEquals(1, $crawler
                ->filter('div.flash_message.success')
                ->count(),
                "Asserting that the response page contains a success flash message");
        $this->assertEquals(1, $crawler
                ->filter('td:contains("Rue de la Paix, 50")')
                ->count(),
                "Asserting that the page contains the new address");
        
    }
    
    /**
     * @depends testCreateAddress
     */
    public function testUpdateAddress()
    {
        $this->refreshPerson();
        $address = self::$person->getLastAddress();
        
        $crawler = $this->client->request('GET', '/fr/person/'.self::$person->getId()
                .'/address/'.$address->getId().'/edit');
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        
        $form = $crawler->selectButton('Envoi')->form(array(
            'address[streetAddress1]' => 'Rue du Trou Normand, 15',
            'address[validFrom]'      => '15-01-2015'
        ));
        
        $this->client->submit($form);
        
        $crawler = $this->client->followRedirect();
        
        $this->assertRegexp('|/fr/person/[0-9]{1,}/address/list|', 
                $this->client->getHistory()->current()->getUri(),
                "assert that the current page is on |/fr/person/[0-9]{1,}/address/list|");
        $this->assertGreaterThan(0, $crawler
                ->filter('div.flash_message.success')
                ->count(),
                "Asserting that the response page contains a success flash message");
        $this->assertEquals(1, $crawler
                ->filter('td:contains("Rue du Trou Normand")')
                ->count(),
                "Asserting that the page contains the new address");
    }
    
    
    
}
