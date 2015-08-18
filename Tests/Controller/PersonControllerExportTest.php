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
use Symfony\Component\Security\Core\Role\Role;

/**
 * Tests for the export of a person
 */

class PersonControllerExportTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManagerInterface The entity manager */
    private $em;

    /**
     * Prepare the client and the entity manager. The client send a Gest requestion 
     * on the route chill_person_export.
    */
    protected function setUp()
    {
        $this->client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center a_social',
           'PHP_AUTH_PW'   => 'password',
        ));

        $exportUrl = $this->client->getContainer()->get('router')->generate('chill_person_export',
            array('_locale' => 'fr'));

        $this->client->request('GET', $exportUrl);

        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Test the format of the exportAction :
     * - check if the file has the csv format
     * - check if each row as the same number of cell than the first row
     * - check if the number of row of the csv file is the same than the number of persons
     * 
     * @return The content of the export
     */
    public function testExportActionFormat()
    {
        $response = $this->client->getResponse();

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


        $chillSecurityHelper = $this->client->getContainer()->get('chill.main.security.authorization.helper');
        $user = $this->client->getContainer()->get('security.context')->getToken()->getUser();

        $reachableCenters = $chillSecurityHelper->getReachableCenters($user,
            new Role('CHILL_PERSON_SEE'));

        $personRepository = $this->em->getRepository('ChillPersonBundle:Person');
        $qb = $personRepository->createQueryBuilder('p');
        $qb->where($qb->expr()->in('p.center', ':centers'))
            ->setParameter('centers', $reachableCenters);
        $persons = $qb->getQuery()->getResult();


        $this->assertTrue(
            $numberOfRows == (sizeof($persons)),
            'The csv file has a number of row equivalent than the number of '
            . 'person in the db'
        );

        return $content;
    }


    /** 
     * Check that the export file do not contain a person form center B
     *
     * @depends testExportActionFormat
     */
    public function testExportActionNotContainingPersonFromCenterB($content)
    {
        $centerB = $this->em->getRepository('ChillMainBundle:Center')
            ->findOneBy(array('name' => 'Center B'));

        $person = $this->em->getRepository('ChillPersonBundle:Person')
            ->findOneBy(array('center' => $centerB));

        $this->assertFalse(strpos($person->getFirstName(), $content));
    }

    /** 
     * Check that the export file contains information about a random person
     * of center A
     * @depends testExportActionFormat
     */
    public function testExportActionContainsARandomPersonFromCenterA($content)
    {
        $centerA = $this->em->getRepository('ChillMainBundle:Center')
            ->findOneBy(array('name' => 'Center A'));

        $person = $this->em->getRepository('ChillPersonBundle:Person')
            ->findOneBy(array('center' => $centerA));

        $this->assertContains($person->getFirstName(), $content);
        $this->assertContains($person->getLastName(), $content);
        $this->assertContains($person->getGender(), $content);

        $this->markTestIncomplete('Test other information of the person');
    }
}
