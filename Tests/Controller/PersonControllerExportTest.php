<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2015, Champs Libres Cooperative SCRLFS, 
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

/**
 * Tests for the export of a person
 */
class PersonControllerExportTest extends WebTestCase
{
    /**
     * Return an authenticated client, used for browsing and testing pages.
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
     * Test the exportAction that returns a csv files containing all the persons.
     * 
     * The test is the following :
     * - check if the file has the csv format
     * - check if the number of row of the csv file is the same than the number of persons
     * 
     * @return \Symfony\Component\BrowserKit\Client
     */
    public function testExportAction()
    {
        $client = $this->getAuthenticatedClient();
        $exportUrl = $client->getContainer()->get('router')->generate('chill_person_export',
            array('_locale' => 'fr'));

        $client->request('GET', $exportUrl);
        $response = $client->getResponse();

        $this->assertTrue(
            strpos($response->headers->get('Content-Type'),'text/csv') !== false,
            'The csv file is well received');
        
        $content = $response->getContent();
        $rows = (explode("\n", $content));

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $persons = $em->getRepository('ChillPersonBundle:Person')->findAll();

        if($rows[sizeof($rows) -1] == "") {
             array_pop($rows);
        }

        $this->assertTrue(
            sizeof($rows) == (sizeof($persons) + 1),
            'The csv file has a number of row equivalent than the number of person in the db'
        );
    }
}
