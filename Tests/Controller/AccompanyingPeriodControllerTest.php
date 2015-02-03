<?php

/*
 * Chill is a software for social workers
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
use Chill\PersonBundle\Entity\PersonHistoryFile as AccompanyingPeriod;
use Chill\PersonBundle\Entity\Person;
use Chill\PersonBundle\Entity\AccompanyingPeriod\ClosingMotive;

/**
 * Test the creation or deletion of accompanying periods
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class AccompanyingPeriodControllerTest extends WebTestCase
{
    /**
     *
     * @var \Symfony\Component\BrowserKit\Client
     */
    private $client;
    
    /**
     *
     * @var Person
     */
    private $person;
    
    /**
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private static $em;
    
    const OPENING_INPUT = 'cl_chill_personbundle_personhistoryfile[date_opening]';
    const CLOSING_INPUT = 'cl_chill_personbundle_personhistoryfile[date_closing]';
    const CLOSING_MOTIVE_INPUT = 'cl_chill_personbundle_personhistoryfile[closingMotive]';
    
    public static function setUpBeforeClass()
    {
        static::bootKernel();
        
        static::$em = static::$kernel->getContainer()
              ->get('doctrine.orm.entity_manager');
    }
    
    public function setUp()
    {
        $this->client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center a_social',
           'PHP_AUTH_PW'   => 'password',
        ));
        
        $this->person = (new Person(new \DateTime('2015-01-05')))
              ->setFirstName('Roland')
              ->setLastName('Gallorime')
              ->setGenre(Person::GENRE_MAN)
              ;

        static::$em->persist($this->person);
        static::$em->flush();
    }
    
    public function tearDown()
    {
       static::$em->refresh($this->person);
       static::$em->remove($this->person);
       
       static::$em->flush();
    }
    
    private function generatePeriods(array $periods)
    {
        foreach ($periods as $periodDef) {
            $period = new AccompanyingPeriod(new \DateTime($periodDef['openingDate']));
            
            if (array_key_exists('closingDate', $periodDef)) {
                if (!array_key_exists('closingMotive', $periodDef)) {
                    throw new \LogicalException('you must define a closing '
                          . 'motive into your periods fixtures');
                }
                
                $period->setDateClosing(new \DateTime($periodDef['closingDate']))
                      ->setClosingMotive($periodDef['closingMotive']);
            }
             
            $this->person->addHistoryFile($period);
            
            static::$em->persist($period);
            
        }
        
        static::$em->flush();
    }
    
    private function getLastValueOnClosingMotive(\Symfony\Component\DomCrawler\Form $form)
    {
        return end($form->get(self::CLOSING_MOTIVE_INPUT)
              ->availableOptionValues());
    }
    
    private function getRandomClosingMotive()
    {
        $motives = static::$em
                ->getRepository('ChillPersonBundle:AccompanyingPeriod\ClosingMotive')
                ->findAll();
        return end($motives);
    }
    
    /**
     * Test the closing of a periods
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and we fill the close form (at /en/person/[id]/history/close
     *      with : dateClosing: 2015-02-01
     *      with : the last closing motive in list
     * Then the response should redirect to history view
     * And the next page should have a `.error` element present in page
     * 
     * @todo
     */
    public function testClosingCurrentPeriod()
    {
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/close');
        
        $form = $crawler->selectButton('Submit')->form();

        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue((new \DateTime('2015-02-01'))->format('d-m-Y'));
        
        $cr = $this->client->submit($form);
        
        $this->assertTrue($this->client->getResponse()->isRedirect(
              '/en/person/'.$this->person->getId().'/history'),
              'the server redirects to /history page');
        $this->assertGreaterThan(0, $this->client->followRedirect()
                ->filter('.success')->count(),
              "a 'success' element is shown");
    }
    
    /**
     * Test the closing of a periods
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and we fill the close form (at /en/person/[id]/history/close
     *      with : dateClosing: 2014-01-01
     *      with : the last closing motive in list
     * Then the response should redirect to history view
     * And the next page should have a `.error` element present in page
     * 
     * @todo
     */
    public function testClosingCurrentPeriodWithDateClosingBeforeOpeningFails()
    {
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/close');
        
        $form = $crawler->selectButton('Submit')->form();

        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue((new \DateTime('2014-01-01'))->format('d-m-Y'));
        
        $crawlerResponse = $this->client->submit($form);
        
        $this->assertFalse($this->client->getResponse()->isRedirect(),
              'the server stays on the /close page');
        $this->assertGreaterThan(0, $crawlerResponse
              ->filter('.error')->count(),
              "an '.error' element is shown");
    }
    
    /**
     * Test the creation of a new period 
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and we create a new period
     *      with : dateClosing: 2014-12-31
     *      with : dateOpening: 2014-01-01
     *      with : the last closing motive in list
     * Then the response should redirect to history view
     */
    public function testAddNewPeriodBeforeActual()
    {
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/create');
        
        $form = $crawler->selectButton('Submit')->form();;
        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue('31-12-2014');
        $form->get(self::OPENING_INPUT)
              ->setValue('01-01-2014');
        
        $this->client->submit($form);
        
        $this->assertTrue($this->client->getResponse()->isRedirect(
              '/en/person/'.$this->person->getId().'/history'),
              'the server redirects to /history page');
        $this->assertGreaterThan(0, $this->client->followRedirect()
              ->filter('.success')->count(),
              "a 'success' element is shown");
    }
    
    /**
     * Create a period with closing after current fails
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and we create a new period
     *      with : dateClosing: 2015-02-01 (after 2015-01-05)
     *      with : dateOpening: 2014-12-31
     *      with : the last closing motive in list
     * Then the response should not redirect to any page
     * and an error element is shown
     * 
     * @todo
     */
    public function testCreatePeriodWithClosingAfterCurrentFails()
    {
        
        $this->markTestSkipped('this feature is not yet implemented');
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/create');
        
        $form = $crawler->selectButton('Submit')->form();;
        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue('01-02-2015');
        $form->get(self::OPENING_INPUT)
              ->setValue('31-12-2014');
        
        $crawler = $this->client->submit($form);
        
        $this->assertFalse($this->client->getResponse()->isRedirect(),
              'the server stay on form page');
        $this->assertGreaterThan(0, $crawler->filter('.error')->count(),
              "an 'error' element is shown");
    }
    
    /**
     * Create a period after a current opened period fails
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and we create a new period
     *      with : dateClosing: 2015-03-01
     *      with : dateOpening: 2015-02-01
     *      with : the last closing motive in list
     * Then the response should not redirect to any page
     * and an error element is shown
     * 
     * @todo
     */
    public function testCreatePeriodWithOpeningAndClosingAfterCurrentFails()
    {
        
        $this->markTestSkipped('this feature is not yet implemented');
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/create');
        
        $form = $crawler->selectButton('Submit')->form();;
        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue('01-03-2015');
        $form->get(self::OPENING_INPUT)
              ->setValue('01-02-2015');
        
        $crawler = $this->client->submit($form);
        
        $this->assertFalse($this->client->getResponse()->isRedirect(),
              'the server stay on form page');
        $this->assertGreaterThan(0, $crawler->filter('.error')->count(),
              "an 'error' element is shown");
    }
    
    /**
     * create a period with date end between another period must fails
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and that this person has another accompanying period between 2014-01-01 and 2014-12-31
     * and we create a new period
     *      with : dateClosing: 2014-16-01
     *      with : dateOpening: 2013-01-01
     *      with : the last closing motive in list
     * Then the response should not redirect 
     * and a error element is shown on the response page
     */
    public function testCreatePeriodWithDateEndBetweenAnotherPeriodFails()
    {
        
        $this->generatePeriods(array(
            [
                'openingDate' => '2014-01-01',
                'closingDate' => '2014-12-31',
                'closingMotive' => $this->getRandomClosingMotive()
            ]
        ));
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/create');
        
        $form = $crawler->selectButton('Submit')->form();;
        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue('31-12-2014');
        $form->get(self::OPENING_INPUT)
              ->setValue('01-02-2015');
        
        $crawlerResponse = $this->client->submit($form);
        
        $this->assertFalse($this->client->getResponse()->isRedirect(),
              'the server stay on form page');
        $this->assertGreaterThan(0, $crawlerResponse->filter('.error')->count(),
              "an 'error' element is shown");
    }
    
    /**
     * create a period with date closing after opening fails
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and we create a new period 
     *      with : dateClosing: 2014-01-01 (before opening)
     *      with : dateOpening: 2015-01-01
     *      with : the last closing motive in list
     * Then the response should redirect to history view
     */
    public function testCreatePeriodWithClosingBeforeOpeningFails()
    {
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/create');
        
        $form = $crawler->selectButton('Submit')->form();;
        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue('01-01-2014');
        $form->get(self::OPENING_INPUT)
              ->setValue('01-01-2015');
        
        $crawler = $this->client->submit($form);
        
        $this->assertFalse($this->client->getResponse()->isRedirect(),
              'the server stay on form page');
        $this->assertGreaterThan(0, $crawler->filter('.error')->count(),
              "an 'error' element is shown");
    }
    
     /**
     * create a period with date closing and date opening inside another period 
     * fails
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and that this person has another accompanying period between 2014-01-01 and 2014-12-31
     * and we create a new period
     *      with : dateClosing: 2014-02-01
     *      with : dateOpening: 2014-03-01
     *      with : the last closing motive in list
     * Then the response should not redirect 
     * and a error element is shown on the response page
     */
    public function testCreatePeriodAfterOpeningFails()
    {
        $this->generatePeriods(array(
            [
                'openingDate' => '2014-01-01',
                'closingDate' => '2014-12-31',
                'closingMotive' => $this->getRandomClosingMotive()
            ]
        ));
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/create');
        
        $form = $crawler->selectButton('Submit')->form();;
        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue('2014-02-01');
        $form->get(self::OPENING_INPUT)
              ->setValue('01-03-2014');
        
        $crawlerResponse = $this->client->submit($form);
        
        $this->assertFalse($this->client->getResponse()->isRedirect(),
              'the server stay on form page');
        $this->assertGreaterThan(0, $crawlerResponse->filter('.error')->count(),
              "an 'error' element is shown");
    }
    
    /**
     * Create a period with dateOpening between another period must fails
     * 
     * Given that a person as an accompanying period opened since 2015-01-05
     * and that this person has another accompanying period between 2014-01-01 and 2014-12-31
     * and we create a new period
     *      with : dateClosing: 2015-01-01
     *      with : dateOpening: 2014-06-01
     *      with : the last closing motive in list
     * Then the response should not redirect 
     * and a error element is shown on the response page
     */
    public function testCreatePeriodWithDateOpeningBetweenAnotherPeriodFails()
    {
        
        $this->generatePeriods(array(
            [
                'openingDate' => '2014-01-01',
                'closingDate' => '2014-12-31',
                'closingMotive' => $this->getRandomClosingMotive()
            ]
        ));
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/create');
        
        $form = $crawler->selectButton('Submit')->form();;
        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue('2015-01-01');
        $form->get(self::OPENING_INPUT)
              ->setValue('01-06-2014');
        
        $crawlerResponse = $this->client->submit($form);
        
        $this->assertFalse($this->client->getResponse()->isRedirect(),
              'the server stay on form page');
        $this->assertGreaterThan(0, $crawlerResponse->filter('.error')->count(),
              "an 'error' element is shown");
    }
    
    
}
