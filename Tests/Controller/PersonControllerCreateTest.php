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

namespace Chill\PersonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

/**
 * Test creation and deletion for persons
 */
class PersonControllerCreateTest extends WebTestCase
{
    
    const FIRSTNAME_INPUT = 'chill_personbundle_person_creation[firstName]';
    const LASTNAME_INPUT = "chill_personbundle_person_creation[lastName]";
    const GENDER_INPUT = "chill_personbundle_person_creation[gender]";
    const BIRTHDATE_INPUT = "chill_personbundle_person_creation[birthdate]";
    const CREATEDATE_INPUT = "chill_personbundle_person_creation[creation_date]";
    const CENTER_INPUT = "chill_personbundle_person_creation[center]";
    
    const LONG_TEXT = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla. Class aptent taciti sociosq. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta.Mauris massa. Vestibulum lacinia arcu eget nulla. Class aptent taciti sociosq.";
    
    /**
     * return an authenticated client, useful for submitting form
     * 
     * @return \Symfony\Component\BrowserKit\Client
     */
    private function getAuthenticatedClient($username = 'center a_social')
    {
        return static::createClient(array(), array(
           'PHP_AUTH_USER' => $username,
           'PHP_AUTH_PW'   => 'password',
        ));
    }
    
    /**
     * 
     * @param Form $creationForm
     */
    private function fillAValidCreationForm(Form &$creationForm, 
          $firstname = 'God', $lastname = 'Jesus')
    {
        $creationForm->get(self::FIRSTNAME_INPUT)->setValue($firstname);
        $creationForm->get(self::LASTNAME_INPUT)->setValue($lastname);
        $creationForm->get(self::GENDER_INPUT)->select("man");
        $date = new \DateTime('1947-02-01');
        $creationForm->get(self::BIRTHDATE_INPUT)->setValue($date->format('d-m-Y'));
        
        return $creationForm;
    }
    
    /**
     * Test the "add a person" page : test that required elements are present
     * 
     * see https://redmine.champs-libres.coop/projects/chillperson/wiki/Test_plan_for_page_%22add_a_person%22
     */
    public function testAddAPersonPage()
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/person/new');
        
        $this->assertTrue($client->getResponse()->isSuccessful(), 
              "The page is accessible at the URL /{_locale}/person/new");
        $form = $crawler->selectButton("Ajouter la personne")->form();
           
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Form', $form, 
              'The page contains a butto ');
        $this->assertTrue($form->has(self::FIRSTNAME_INPUT),
              'The page contains a "firstname" input');
        $this->assertTrue($form->has(self::LASTNAME_INPUT),
              'The page contains a "lastname" input');
        $this->assertTrue($form->has(self::GENDER_INPUT),
              'The page contains a "gender" input');
        $this->assertTrue($form->has(self::BIRTHDATE_INPUT), 
              'The page has a "date of birth" input');
        $this->assertTrue($form->has(self::CREATEDATE_INPUT),
              'The page contains a "creation date" input');
        
        $genderType = $form->get(self::GENDER_INPUT);
        $this->assertEquals('radio', $genderType->getType(), 
              'The gender input has two radio button: man and women');
        $this->assertEquals(2, count($genderType->availableOptionValues()), 
              'The gender input has two radio button: man and women');
        $this->assertTrue(in_array('man', $genderType->availableOptionValues()), 
              'gender has "homme" option');
        $this->assertTrue(in_array('woman', $genderType->availableOptionValues()), 
              'gender has "femme" option');
        $this->assertFalse($genderType->hasValue(), 'The gender input is not checked');
        
        $today = new \DateTime();
        $this->assertEquals($today->format('d-m-Y'), $form->get(self::CREATEDATE_INPUT)
              ->getValue(), 'The creation date input has the current date by default');
        
        return $form;
    }
    
    /**
     * 
     * @param Form $form
     * @depends testAddAPersonPage
     */
    public function testFirstnameTooLong(Form $form)
    {
        $client = $this->getAuthenticatedClient();
        $this->fillAValidCreationForm($form);
        $form->get(self::FIRSTNAME_INPUT)->setValue(mb_substr(self::LONG_TEXT, 0, 256));
        $crawler = $client->submit($form);
        
        $this->assertEquals(1, $crawler->filter('.error')->count(),
              "An error message is shown if we fill more than 255 characters in firstname");
    }
    
    /**
     * 
     * @param Form $form
     * @depends testAddAPersonPage
     */
    public function testLastnameTooLong(Form $form)
    {
        $this->fillAValidCreationForm($form);
        $form->get(self::LASTNAME_INPUT)->setValue(mb_substr(self::LONG_TEXT, 0, 256));
        $crawler = $this->getAuthenticatedClient()->submit($form);
        
        $this->assertEquals(1, $crawler->filter('.error')->count(),
            "An error message is shown if we fill more than 255 characters in lastname");
    }
    
    /**
     * 
     * @param Form $form
     * @depends testAddAPersonPage
     */
    public function testGenderIsNull(Form $form)
    {
        $this->fillAValidCreationForm($form);
        $form->get(self::GENDER_INPUT)->disableValidation()->setValue(NULL);
        $crawler = $this->getAuthenticatedClient()->submit($form);
        
        $this->assertEquals(1, $crawler->filter('.error')->count(),
            'A message is shown if gender is not set');
    }
    
    /**
     * Test the creation of a valid person.
     * 
     * @param Form $form
     * @return string The id of the created person
     * @depends testAddAPersonPage
     */
    public function testValidForm(Form $form)
    {
        $this->fillAValidCreationForm($form);
        $client = $this->getAuthenticatedClient();
        $client->submit($form);
        
        $this->assertTrue($client->getResponse()->isRedirect(), 
            "a valid form redirect to url /{_locale}/person/{personId}/general/edit");
        $client->followRedirect();
        
        // visualize regexp here : http://jex.im/regulex/#!embed=false&flags=&re=%2Ffr%2Fperson%2F[1-9][0-9]*%2Fgeneral%2Fedit%24
        $this->assertRegExp('|/fr/person/[1-9][0-9]*/general/edit$|', 
            $client->getHistory()->current()->getUri(),
            "a valid form redirect to url /{_locale}/person/{personId}/general/edit");

        $regexPersonId = null;
        preg_match("/person\/([1-9][0-9]*)\/general\/edit$/",
            $client->getHistory()->current()->getUri(), $regexPersonId);
        return $regexPersonId[1];
    }

    /**
     * Test if, for a given person if its person view page (at the url
     * fr/person/$personID/general) is accessible
     *
     * @param string|int $personId The is of the person
     * @depends testValidForm
     */
    public function testPersonViewAccessible($personId)
    {
        $client = $this->getAuthenticatedClient();
        $client->request('GET', '/fr/person/'.$personId.'/general');

        $this->assertTrue($client->getResponse()->isSuccessful(), 
            "The person view page is accessible at the URL"
            . "/{_locale}/person/{personID}/general");
    }
    
    /**
     * test adding a person with a user with multi center
     * is valid
     */
    public function testValidFormWithMultiCenterUser()
    {
        $client = $this->getAuthenticatedClient('multi_center');
        
        $crawler = $client->request('GET', '/fr/person/new');
        
        $this->assertTrue($client->getResponse()->isSuccessful(), 
              "The page is accessible at the URL /{_locale}/person/new");
        $form = $crawler->selectButton("Ajouter la personne")->form();
        
        $this->fillAValidCreationForm($form, 'roger', 'rabbit');
        
        $this->assertTrue($form->has(self::CENTER_INPUT),
              'The page contains a "center" input');
        $centerInput = $form->get(self::CENTER_INPUT);
        $availableValues = $centerInput->availableOptionValues();
        $lastCenterInputValue = end($availableValues);
        $centerInput->setValue($lastCenterInputValue);
        
        $client->submit($form);
        
        $this->assertTrue($client->getResponse()->isRedirect(), 
              "a valid form redirect to url /{_locale}/person/{personId}/general/edit");
        $client->followRedirect();
        $this->assertRegExp('|/fr/person/[1-9][0-9]*/general/edit$|', 
              $client->getHistory()->current()->getUri(), 
              "a valid form redirect to url /{_locale}/person/{personId}/general/edit");
             
    }
    
    public function testReviewExistingDetectionInversedLastNameWithFirstName()
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/person/new');
        
        //test the page is loaded before continuing
        $this->assertTrue($client->getResponse()->isSuccessful());
        
        $form = $crawler->selectButton("Ajouter la personne")->form();
        $form = $this->fillAValidCreationForm($form, 'Charline', 'dd');
        $client->submit($form);
        
        $this->assertContains('Depardieu', $client->getCrawler()->text(), 
                "check that the page has detected the lastname of a person existing in database");
        
        //inversion
        $form = $crawler->selectButton("Ajouter la personne")->form();
        $form = $this->fillAValidCreationForm($form, 'dd', 'Charline');
        $client->submit($form);
        
        $this->assertContains('Depardieu', $client->getCrawler()->text(), 
                "check that the page has detected the lastname of a person existing in database");
    }
    
    public static function tearDownAfterClass()
    {
        static::bootKernel();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        //remove two people created during test
        $jesus = $em->getRepository('ChillPersonBundle:Person')
              ->findOneBy(array('firstName' => 'God'));
        if ($jesus !== NULL) {
            $em->remove($jesus);
        }
        
        $jesus2 = $em->getRepository('ChillPersonBundle:Person')
              ->findOneBy(array('firstName' => 'roger'));
        if ($jesus2 !== NULL) {
            $em->remove($jesus2);
        }
        $em->flush();
    }
}
