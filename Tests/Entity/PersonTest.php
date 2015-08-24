<?php

/*
 * Chill is a software for social workers
 * 
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>, <info@champs-libres.coop>
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

namespace Chill\PersonBundle\Tests\Entity;

use Chill\PersonBundle\Entity\Person;
use Chill\PersonBundle\Entity\AccompanyingPeriod;

/**
 * Unit tests for the person Entity
 */
class PersonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the creation of an accompanying, its closure and the access to 
     * the current accompaniying period via the getCurrentAccompanyingPeriod
     * function.
     */
    public function testGetCurrentAccompanyingPeriod()
    {
        $d = new \DateTime('yesterday'); 
        $p = new Person($d);
        
        $period = $p->getCurrentAccompanyingPeriod();
        
        $this->assertInstanceOf('Chill\PersonBundle\Entity\AccompanyingPeriod', $period);
        $this->assertTrue($period->isOpen());
        $this->assertEquals($d, $period->getOpeningDate());
        
        //close and test
        $period->setClosingDate(new \DateTime('tomorrow'));
        
        $shouldBeNull = $p->getCurrentAccompanyingPeriod();
        $this->assertNull($shouldBeNull);
    }
    
    /**
     * Test if the getAccompanyingPeriodsOrdered function return a list of
     * periods ordered ascendency.
     */
    public function testAccompanyingPeriodOrderWithUnorderedAccompanyingPeriod()
    {       
        $d = new \DateTime("2013/2/1");
        $p = new Person($d);
        
        $e = new \DateTime("2013/3/1");
        $period = $p->getCurrentAccompanyingPeriod()->setClosingDate($e);
        $p->close($period);
        
        $f = new \DateTime("2013/1/1");
        $p->open(new AccompanyingPeriod($f));
        
        $g = new \DateTime("2013/4/1"); 
        $period = $p->getCurrentAccompanyingPeriod()->setClosingDate($g);
        $p->close($period);
        
        $r = $p->getAccompanyingPeriodsOrdered();
        
        $date = $r[0]->getOpeningDate()->format('Y-m-d');
        
        $this->assertEquals($date, '2013-01-01');
    }
    
    /**
     * Test if the getAccompanyingPeriodsOrdered function, for periods
     * starting at the same time order regarding to the closing date.
     */
    public function testAccompanyingPeriodOrderSameDateOpening() {
        $d = new \DateTime("2013/2/1");
        $p = new Person($d);
        
        $g = new \DateTime("2013/4/1"); 
        $period = $p->getCurrentAccompanyingPeriod()->setClosingDate($g);
        $p->close($period);
        
        $f = new \DateTime("2013/2/1");
        $p->open(new AccompanyingPeriod($f));
        
        $e = new \DateTime("2013/3/1");
        $period = $p->getCurrentAccompanyingPeriod()->setClosingDate($e);
        $p->close($period);

        $r = $p->getAccompanyingPeriodsOrdered();
        
        $date = $r[0]->getClosingDate()->format('Y-m-d');
        
        $this->assertEquals($date, '2013-03-01');
    }
    
    /**
     * Test if the function checkAccompanyingPeriodIsNotCovering returns
     * the good constant when two periods are collapsing : a period
     * is covering another one : start_1 < start_2 & end_2 < end_1
     */
    public function testDateCoveringWithCoveringAccompanyingPeriod() {
        $d = new \DateTime("2013/2/1");
        $p = new Person($d);
        
        $e = new \DateTime("2013/3/1");
        $period = $p->getCurrentAccompanyingPeriod()->setClosingDate($e);
        $p->close($period);
        
        $f = new \DateTime("2013/1/1");
        $p->open(new AccompanyingPeriod($f));
        
        $g = new \DateTime("2013/4/1"); 
        $period = $p->getCurrentAccompanyingPeriod()->setClosingDate($g);
        $p->close($period);
        
        $r = $p->checkAccompanyingPeriodsAreNotCollapsing();
        
        $this->assertEquals($r['result'], Person::ERROR_PERIODS_ARE_COLLAPSING);
    }
    
    /**
     * Test if the function checkAccompanyingPeriodIsNotCovering returns
     * the good constant when two periods are collapsing : a period is open
     * before an existing period
     */
    public function testNotOpenAFileReOpenedLater() {
        $d = new \DateTime("2013/2/1");
        $p = new Person($d);
        
        $e = new \DateTime("2013/3/1");
        $period = $p->getCurrentAccompanyingPeriod()->setClosingDate($e);
        $p->close($period);
        
        $f = new \DateTime("2013/1/1");
        $p->open(new AccompanyingPeriod($f));
        
        $r = $p->checkAccompanyingPeriodsAreNotCollapsing();
        
        $this->assertEquals($r['result'], Person::ERROR_ADDIND_PERIOD_AFTER_AN_OPEN_PERIOD);
    }
}
