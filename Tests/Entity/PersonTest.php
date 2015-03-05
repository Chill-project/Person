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
 * Unit tests on person
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PersonTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCurrentAccompanyingPeriod()
    {
        $d = new \DateTime('yesterday'); 
        $p = new Person($d);
        
        $period = $p->getCurrentAccompanyingPeriod();
        
        $this->assertInstanceOf('Chill\PersonBundle\Entity\AccompanyingPeriod', $period);
        $this->assertTrue($period->isOpen());
        $this->assertEquals($d, $period->getDateOpening());
        
        //close and test
        $period->setDateClosing(new \DateTime('tomorrow'));
        
        $shouldBeNull = $p->getCurrentAccompanyingPeriod();
        $this->assertNull($shouldBeNull);
        
    }
    
    public function testAccompanyingPeriodOrderWithUnorderedAccompanyingPeriod() {
        $d = new \DateTime(); 
        $d->setDate(2013, 2, 1);
        $p = new Person($d);
        
        $e = new \DateTime(); 
        $e->setDate(2013, 3, 1);
        $period = $p->getCurrentAccompanyingPeriod()->setDateClosing($e);
        $p->close($period);
        
        $f = new \DateTime(); 
        $f->setDate(2013, 1, 1);
        $p->open(new AccompanyingPeriod($f));
        
        $g = new \DateTime(); 
        $g->setDate(2013, 4, 1); 
        $period = $p->getCurrentAccompanyingPeriod()->setDateClosing($g);
        $p->close($period);
        
        $r = $p->getAccompanyingPeriodsOrdered();
        
        $date = $r[0]->getDateOpening()->format('Y-m-d');
        
        $this->assertEquals($date, '2013-01-01');
    }
    
    
    public function testAccompanyingPeriodOrderSameDateOpening() {
        $d = new \DateTime(); 
        $d->setDate(2013, 2, 1);
        $p = new Person($d);
        
        $e = new \DateTime(); 
        $e->setDate(2013, 3, 1);
        $period = $p->getCurrentAccompanyingPeriod()->setDateClosing($e);
        $p->close($period);
        
        $f = new \DateTime(); 
        $f->setDate(2013, 2, 1);
        $p->open(new AccompanyingPeriod($f));
        
        $g = new \DateTime(); 
        $g->setDate(2013, 4, 1); 
        $period = $p->getCurrentAccompanyingPeriod()->setDateClosing($g);
        $p->close($period);

        $r = $p->getAccompanyingPeriodsOrdered();
        
        $date = $r[0]->getDateClosing()->format('Y-m-d');
        
        $this->assertEquals($date, '2013-03-01');
    }
    
    public function testDateCoveringWithCoveringAccompanyingPeriod() {
        $d = new \DateTime(); 
        $d->setDate(2013, 2, 1);
        $p = new Person($d);
        
        $e = new \DateTime(); 
        $e->setDate(2013, 3, 1);
        $period = $p->getCurrentAccompanyingPeriod()->setDateClosing($e);
        $p->close($period);
        
        $f = new \DateTime(); 
        $f->setDate(2013, 1, 1);
        $p->open(new AccompanyingPeriod($f));
        
        $g = new \DateTime(); 
        $g->setDate(2013, 4, 1); 
        $period = $p->getCurrentAccompanyingPeriod()->setDateClosing($g);
        $p->close($period);
        
        $r = $p->checkAccompanyingPeriodIsNotCovering();
        
        $this->assertEquals($r['result'], Person::ERROR_OPENING_IS_INSIDE_CLOSING);
    }
    
    public function testNotOpenAFileReOpenedLater() {
        $d = new \DateTime(); 
        $d->setDate(2013, 2, 1);
        $p = new Person($d);
        
        $e = new \DateTime(); 
        $e->setDate(2013, 3, 1);
        $period = $p->getCurrentAccompanyingPeriod()->setDateClosing($e);
        $p->close($period);
        
        $f = new \DateTime(); 
        $f->setDate(2013, 1, 1);
        $p->open(new AccompanyingPeriod($f));
        
        $r = $p->checkAccompanyingPeriodIsNotCovering();
        
        $this->assertEquals($r['result'], Person::ERROR_OPENING_NOT_CLOSED_IS_BEFORE_NEW_LINE);
    }
}
