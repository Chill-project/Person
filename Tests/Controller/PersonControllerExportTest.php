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
     * - check if each row as the same number of cell than the first row
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
        $rows = str_getcsv($content, "\n");
        $headerRow = array_pop($rows);
        $header = str_getcsv($headerRow);
        $headerSize = sizeof($header);
        $numberOfRows = 0;

        foreach ($rows as $row) {
            $rowContent = str_getcsv($row);

            $this->assertTrue(
                sizeof($rowContent) == $headerSize,
                'Each row of the csv contains the good number of elements ('
                . 'regarding to the first row');

            $numberOfRows ++;
        }

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $persons = $em->getRepository('ChillPersonBundle:Person')->findAll();

        $this->assertTrue(
            $numberOfRows == (sizeof($persons)),
            'The csv file has a number of row equivalent than the number of '
            . 'person in the db'
        );
    }
}
