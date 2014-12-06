<?php

namespace Chill\PersonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test creation and deletion for persons
 */
class PersonControllerTest extends WebTestCase
{
    /**
     * Test the "add a person" page
     * 
     * see https://redmine.champs-libres.coop/projects/chillperson/wiki/Test_plan_for_page_%22add_a_person%22
     */
    public function testAddAPersonPage()
    {
        $client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center a_social',
           'PHP_AUTH_PW'   => 'password',
        ));
        
        $crawler = $client->request('GET', '/fr/person/new');
        
        $this->assertTrue($client->getResponse()->isSuccessful(), 
              "The page is accessible at the URL /{_locale}/person/new");
        $form = $crawler->selectButton("Ajouter la personne")->form();
        
        $this->assertTrue($form->has('chill_personbundle_person_creation[firstName]'),
              'The page contains a "firstname" input');
        $this->assertTRue($form->has("chill_personbundle_person_creation[lastName]"),
              'The page contains a "lastname" input');
        $this->assertTrue($form->has("chill_personbundle_person_creation[genre]"),
              'The page contains a "gender" input');
        $this->assertTrue($form->has("chill_personbundle_person_creation[dateOfBirth]"), 
              'The page has a "date of birth" input');
        
        
    }

}
