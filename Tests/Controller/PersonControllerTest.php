<?php

namespace Chill\PersonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

/**
 * Test creation and deletion for persons
 */
class PersonControllerTest extends WebTestCase
{
    
    const FIRSTNAME_INPUT = 'chill_personbundle_person_creation[firstName]';
    const LASTNAME_INPUT = "chill_personbundle_person_creation[lastName]";
    const GENRE_INPUT = "chill_personbundle_person_creation[genre]";
    const DATEOFBIRTH_INPUT = "chill_personbundle_person_creation[dateOfBirth]";
    const CREATEDATE_INPUT = "chill_personbundle_person_creation[creation_date]";
    
    const LONG_TEXT = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla. Class aptent taciti sociosq.";
    
    /**
     * return an authenticated client, useful for submitting form
     * 
     * @return \Symfony\Component\BrowserKit\Client
     */
    private function getAuthenticatedClient()
    {
        return static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center a_social',
           'PHP_AUTH_PW'   => 'password',
        ));
    }
    
    /**
     * 
     * @param Form $creationForm
     */
    private function fillAValidCreationForm(Form &$creationForm)
    {
        $creationForm->get(self::FIRSTNAME_INPUT)->setValue("God");
        $creationForm->get(self::LASTNAME_INPUT)->setValue("Jesus");
        $creationForm->get(self::GENRE_INPUT)->select("man");
        $date = new \DateTime('1947-02-01');
        $creationForm->get(self::DATEOFBIRTH_INPUT)->setValue($date->format('d-m-Y'));
        
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
        $this->assertTrue($form->has(self::GENRE_INPUT),
              'The page contains a "gender" input');
        $this->assertTrue($form->has(self::DATEOFBIRTH_INPUT), 
              'The page has a "date of birth" input');
        $this->assertTrue($form->has(self::CREATEDATE_INPUT),
              'The page contains a "creation date" input');
        
        $genderType = $form->get(self::GENRE_INPUT);
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
        $form->get(self::GENRE_INPUT)->disableValidation()->setValue(NULL);
        $crawler = $this->getAuthenticatedClient()->submit($form);
        
        $this->assertEquals(1, $crawler->filter('.error')->count(),
              'A message is shown if gender is not set');
    }
    
    /**
     * 
     * @param Form $form
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
        $this->assertRegExpl('^/fr/person/[0-9]*/general/edit', 
              $client->getResponse()->getHeader('location'), 
              "a valid form redirect to url /{_locale}/person/{personId}/general/edit");
    }
    
    

}
