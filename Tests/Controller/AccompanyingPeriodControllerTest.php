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

        //remove Accompanying periods
        $this->person->close($this->person->getCurrentHistory()
              ->setDateClosing(new \DateTime('2015-01-05')));
        foreach($this->person->getHistories() as $accompanyingPeriod) {
            $this->person->removeHistoryFile($accompanyingPeriod);
        }
        static::$em->persist($this->person);
        static::$em->flush();
    }
    
    public function tearDown()
    {
       //static::$em->refresh($this->person);
       //static::$em->remove($this->person);
       
       //static::$em->flush();
    }
    
    private function generatePeriods(array $periods)
    {
        foreach ($periods as $periodDef) {
            $period = new AccompanyingPeriod(new \DateTime($periodDef['openingDate']));
                  var_dump((new \DateTime($periodDef['openingDate']))->format('d-m-Y'));
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

        $this->markTestSkipped('not implemented fully');

        $this->generatePeriods(array( [
            'openingDate' => '2015-01-05'
           ]
        ));
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/history/close');
        
        $form = $crawler->selectButton('Submit')->form();

        $form->get(self::CLOSING_MOTIVE_INPUT)
              ->setValue($this->getLastValueOnClosingMotive($form));
        $form->get(self::CLOSING_INPUT)
              ->setValue((new \DateTime('2015-02-01'))->format('d-m-Y'));
        var_dump((new \DateTime('2015-02-01'))->format('d-m-Y'));
        $cr = $this->client->submit($form);
        var_dump($cr->text());
        $this->assertTrue($this->client->getResponse()->isRedirect(
              '/en/person/'.$this->person->getId().'/history'),
              'the server redirects to /history page');
        $this->assertGreaterThan(0, $this->client->followRedirect()
              ->filter('.success')->count(),
              "a 'success' message is shown");
    }
    
    
    public function testAddNewPeriodBeforeActual()
    {
        $this->generatePeriods(array(
           [
              'openingDate' => '2015-01-25'
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
              ->setValue('01-01-2014');
        
        $this->client->submit($form);
        
        $this->assertTrue($this->client->getResponse()->isRedirect(
              '/en/person/'.$this->person->getId().'/history'),
              'the server redirects to /history page');
        $this->assertGreaterThan(0, $this->client->followRedirect()
              ->filter('.success')->count(),
              "a 'success' message is shown");
    }
}
