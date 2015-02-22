<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2015 Champs-Libres Coopérative <info@champs-libres.coop>
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

namespace Chill\PersonBundle\Tests\Search;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Test Person search
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PersonSearchTest extends WebTestCase
{
    public function testExpected()
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/search', array(
           'q' => '@person Depardieu'
        ));
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testExpectedNamed()
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/search', array(
           'q' => '@person Depardieu', 'name' => 'person_regular'
        ));
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testSearchByFirstName()
    {
        $crawler = $this->generateCrawlerForSearch('@person firstname:Depardieu');
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testSearchByFirstNameLower()
    {
        $crawler = $this->generateCrawlerForSearch('@person firstname:depardieu');
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testSearchByFirstNamePartim()
    {
        $crawler = $this->generateCrawlerForSearch('@person firstname:Dep');
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testFirstNameAccentued()
    {
        $crawlerSpecial = $this->generateCrawlerForSearch('@person firstname:manço');
        
        $this->assertRegExp('/Manço/', $crawlerSpecial->text());
        
        
        $crawlerNoSpecial = $this->generateCrawlerForSearch('@person firstname:manco');
        
        $this->assertRegExp('/Manço/', $crawlerNoSpecial->text());
    }
    
    public function testSearchByLastName()
    {
        $crawler = $this->generateCrawlerForSearch('@person lastname:Jean');
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testSearchByLastNameLower()
    {
        $crawler = $this->generateCrawlerForSearch('@person lastname:jean');
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testSearchByLastNamePartim()
    {
        $crawler = $this->generateCrawlerForSearch('@person lastname:ean');
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testSearchByLastNameAccented()
    {
        $crawlerSpecial = $this->generateCrawlerForSearch('@person lastname:Gérard');
        
        $this->assertRegExp('/Gérard/', $crawlerSpecial->text());
        
        
        $crawlerNoSpecial = $this->generateCrawlerForSearch('@person lastname:Gerard');
        
        $this->assertRegExp('/Gérard/', $crawlerNoSpecial->text());
    }
    
    public function testSearchCombineFirstnameAndNationality()
    {
        $crawler = $this->generateCrawlerForSearch('@person firstname:Depardieu nationality:RU');
        
        $this->assertRegExp('/Gérard/', $crawler->text());
        //if this is a AND clause, Jean Depardieu should not appears
        $this->assertNotRegExp('/Jean/', $crawler->text(),
              "assert clause firstname and nationality are AND");
    }
    
    public function testSearchCombineLastnameAndFirstName()
    {
        $crawler = $this->generateCrawlerForSearch('@person firstname:Depardieu lastname:Jean');
        
        $this->assertRegExp('/Depardieu/', $crawler->text());
        //if this is a AND clause, Jean Depardieu should not appears
        $this->assertNotRegExp('/Gérard/', $crawler->text(),
              "assert clause firstname and nationality are AND");
    }
    
    public function testSearchDateOfBirth()
    {
        $crawler = $this->generateCrawlerForSearch('@person birthdate:1948-12-27');
        
        $this->assertRegExp('/Gérard/', $crawler->text());
        $this->assertRegExp('/Bart/', $crawler->text());
    }
    
    public function testSearchCombineDateOfBirthAndFirstName()
    {
        $crawler = $this->generateCrawlerForSearch('@person birthdate:1948-12-27 firstname:(Van Snick)');
        
        $this->assertRegExp('/Bart/', $crawler->text());
        $this->assertNotRegExp('/Depardieu/', $crawler->text());
    }
    
    public function testSearchCombineGenreAndFirstName()
    {
        $crawler = $this->generateCrawlerForSearch('@person gender:woman firstname:(Depardieu)');
        
        $this->assertRegExp('/Charline/', $crawler->text());
        $this->assertNotRegExp('/Gérard/', $crawler->text());
    }
    
    public function testSearchMultipleTrigramUseAndClauseInDefault()
    {
        $crawler = $this->generateCrawlerForSearch('@person cha dep');
        
        $this->assertRegExp('/Charline/', $crawler->text());
        $this->assertNotRegExp('/Gérard/', $crawler->text());
        $this->assertNotRegExp('/Jean/', $crawler->text());
    }
    
    
    
    public function testDefaultAccented()
    {
        $crawlerSpecial = $this->generateCrawlerForSearch('@person manço');
        
        $this->assertRegExp('/Manço/', $crawlerSpecial->text());
        
        
        $crawlerNoSpecial = $this->generateCrawlerForSearch('@person manco');
        
        $this->assertRegExp('/Manço/', $crawlerNoSpecial->text());
        
        $crawlerSpecial = $this->generateCrawlerForSearch('@person Étienne');
        
        $this->assertRegExp('/Étienne/', $crawlerSpecial->text());
        
        
        $crawlerNoSpecial = $this->generateCrawlerForSearch('@person etienne');
        
        $this->assertRegExp('/Étienne/', $crawlerNoSpecial->text());
    }
    
    private function generateCrawlerForSearch($pattern)
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/search', array(
           'q' => $pattern
        ));
        
        $this->assertTrue($client->getResponse()->isSuccessful());
        
        return $crawler;
    }
    
    /**
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
}
