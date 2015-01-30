<?php

/*
 * Chill is a suite of a modules, Chill is a software for social workers
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

namespace Chill\PersonBundle\Tests\Controller;

use Chill\PersonBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test the edition of persons
 * 
 * As I am logged in as "center a_social"
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PersonControllerUpdateTest extends WebTestCase
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
    
    /**
     *
     * @var string
     */
    private $editUrl;
    
    /**
     * prepare client and select a random person
     */
    public function setUp()
    {
        static::bootKernel();
        
        $this->person = (new Person())
                ->setLastName("My Beloved")
                ->setFirstName("Jesus")
                ->setGenre(Person::GENRE_MAN);
        
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        $this->em->persist($this->person);
        $this->em->flush();
        
        $this->editUrl = '/en/person/'.$this->person->getId().'/general/edit';
        $this->seeUrl  = '/en/person/'.$this->person->getId().'/general';
        
        $this->client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center a_social',
           'PHP_AUTH_PW'   => 'password',
        ));
    }
    
    /**
     * Test the edit page exist and rendering is successful
     */
    public function testEditPageIsSuccessful()
    {
        $this->client->request('GET', $this->editUrl);
        
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 
                "The person edit form is accessible");
    }
    
    /**
     * test the edition of a field
     * 
     * Given I fill the field with $value
     * And I submit the form
     * Then I am redirected to the 'general' page
     * And the person is updated in the db
     * 
     * @dataProvider validTextFieldsProvider
     * @param string $field
     * @param string $value
     * @param \Closure $callback
     */
    public function testEditTextField($field, $value, \Closure $callback)
    {
        $crawler = $this->client->request('GET', $this->editUrl);
        
        $form = $crawler->selectButton('Submit')
                ->form();
        //transform countries into value if needed
        switch ($field) {
            case 'nationality':
            case 'countryOfBirth':
                if ($value !== NULL) {
                    $country = $this->em->getRepository('ChillMainBundle:Country')
                        ->findOneByCountryCode($value);
                    $transformedValue = $country->getId();
                } else {
                    $transformedValue = NULL;
                }
                break;
            default:
                $transformedValue = $value;
        }
        
        $form->get('chill_personbundle_person['.$field. ']')
                ->setValue($transformedValue);
        
        $this->client->submit($form);
        $this->em->refresh($this->person);
        
        $this->assertTrue($this->client->getResponse()->isRedirect($this->seeUrl),
              'the page is redirected to general view');
        $this->assertEquals($value, $callback($this->person),
              'the value '.$field.' is updated in db');
    }
    
    
    /**
     * 
     * @dataProvider providesInvalidFieldsValues
     * @param string $field
     * @param string $value
     */
    public function testInvalidFields($field, $value)
    {
        $crawler = $this->client->request('GET', $this->editUrl);
        
        $form = $crawler->selectButton('Submit')
                ->form();
        $form->get('chill_personbundle_person['.$field.']')
                ->setValue($value);
        
        $crawler = $this->client->submit($form);
        
        $this->assertFalse($this->client->getResponse()->isRedirect(),
              'the page is not redirected to /general');
        $this->assertGreaterThan(0, $crawler->filter('.error')->count(),
              'a element .error is shown');
    }
    
    /**
     * provide valid values to test, with field name and 
     * a function to find the value back from person entity
     * 
     * @return mixed[]
     */
    public function validTextFieldsProvider()
    { 
        return array(
            ['firstName', 'random Value', function(Person $person) { return $person->getFirstName(); } ],
            ['lastName' , 'random Value', function(Person $person) { return $person->getLastName(); }  ],
            ['placeOfBirth', 'none place', function(Person $person) { return $person->getPlaceOfBirth(); }],
            ['dateOfBirth', '15-12-1980', function(Person $person) { return $person->getDateOfBirth()->format('d-m-Y'); }],
            ['phonenumber', '0123456789', function(Person $person) { return $person->getPhonenumber(); }],
            ['memo', 'jfkdlmq jkfldmsq jkmfdsq', function(Person $person) { return $person->getMemo(); }],
            ['countryOfBirth', 'BE', function(Person $person) { return $person->getCountryOfBirth()->getCountryCode(); }],
            ['nationality', 'FR', function(Person $person) { return $person->getNationality()->getCountryCode(); }],
            ['placeOfBirth', '', function(Person $person) { return $person->getPlaceOfBirth(); }],
            ['dateOfBirth', '', function(Person $person) { return $person->getDateOfBirth(); }],
            ['phonenumber', '', function(Person $person) { return $person->getPhonenumber(); }],
            ['memo', '', function(Person $person) { return $person->getMemo(); }],
            ['countryOfBirth', NULL, function(Person $person) { return $person->getCountryOfBirth(); }],
            ['nationality', NULL, function(Person $person) { return $person->getNationality(); }],
            ['genre', Person::GENRE_WOMAN, function(Person $person) { return $person->getGenre(); }]
        );
    }
    
    public function providesInvalidFieldsValues()
    {
        return array(
            ['firstName', $this->getVeryLongText()],
            ['lastName', $this->getVeryLongText()],
            ['firstName', ''],
            ['lastName', ''],
            ['dateOfBirth', 'false date']
        );
    }
    
    public function tearDown()
    {
        $this->em->remove($this->person);
        $this->em->flush();
    }
    
    private function getVeryLongText()
    {
        return <<<EOT
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse molestie at enim id auctor. Vivamus malesuada elit ipsum, ac mollis ex facilisis sit amet. Phasellus accumsan, quam ut aliquet accumsan, augue ligula consequat erat, condimentum iaculis orci magna egestas eros. In vel blandit sapien. Duis ut dui vitae tortor iaculis malesuada vitae vitae lorem. Morbi efficitur dolor orci, a rhoncus urna blandit quis. Aenean at placerat dui, ut tincidunt nulla. In ultricies tempus ligula ac rutrum. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Fusce urna nibh, placerat vel auctor sed, maximus quis magna. Vivamus quam ante, consectetur vel feugiat quis, aliquet id ante. Integer gravida erat dignissim ante commodo mollis. Donec imperdiet mauris elit, nec blandit dolor feugiat ut. Proin iaculis enim ut tortor pretium commodo. Etiam aliquet hendrerit dolor sed fringilla. Vestibulum facilisis nibh tincidunt dui egestas, vitae congue mi imperdiet. Duis vulputate ultricies lectus id cursus. Fusce bibendum sem dignissim, bibendum purus quis, mollis ex. Cras ac est justo. Duis congue mattis ipsum, vitae sagittis justo dictum sit amet. Duis aliquam pharetra sem, non laoreet ante laoreet ac. Mauris ornare mi tempus rutrum consequat. 
EOT;
    }
}
