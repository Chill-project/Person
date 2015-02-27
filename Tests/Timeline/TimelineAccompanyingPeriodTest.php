<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2015 Champs Libres <info@champs-libres.coop>
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

namespace Chill\PersonBundle\Tests\Timeline;

use Symfony\Bundle\SecurityBundle\Tests\Functional\WebTestCase;
use Chill\PersonBundle\Entity\Person;
use Chill\PersonBundle\Entity\AccompanyingPeriod;

/**
 * This class tests entries are shown for closing and opening 
 * periods in timeline.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class TimelineAccompanyingPeriodTest extends \Chill\PersonBundle\Tests\Controller\AccompanyingPeriodControllerTest
{
    public function testEntriesAreShown() 
    {
        $this->generatePeriods(array(
            [
                'openingDate' => '2014-01-01',
                'closingDate' => '2014-12-31',
                'closingMotive' => $this->getRandomClosingMotive()
            ]
        ));
        
        $crawler = $this->client->request('GET', '/en/person/'
              .$this->person->getId().'/timeline');
        
        $this->assertTrue($this->client->getResponse()->isSuccessful(),
              "the timeline page loads sucessfully");
        $this->assertGreaterThan(0, $crawler->filter('.timeline div')->count(),
              "the timeline page contains multiple div inside a .timeline element");
        $this->assertContains("Une période d'accompagnement a été ouverte",
              $crawler->filter('.timeline')->text(),
              "the text 'une période d'accompagnement a été ouverte' is present");
        $this->assertContains("Une période d'accompagnement a été fermée",
              $crawler->Filter('.timeline')->text(),
              "the text 'Une période d'accompagnement a été fermée' is present");
    }
   
}
