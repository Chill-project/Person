<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>
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

namespace Chill\PersonBundle\Tests\Entity;

use Chill\PersonBundle\Entity\AccompanyingPeriod;

class AccompanyingPeriodTest extends \PHPUnit_Framework_TestCase
{
    public function testClosingIsAfterOpeningConsistency()
    {
        $datetime1 = new \DateTime('now');
        $datetime2 = new \DateTime('tomorrow');
        
        $period = new AccompanyingPeriod($datetime1);
        $period->setDateClosing($datetime2);
        
        $r = $period->isClosingAfterOpening();
        
        $this->assertTrue($r);
    }
    
    public function testClosingIsBeforeOpeningConsistency() {
        $datetime1 = new \DateTime('tomorrow');
        $datetime2 = new \DateTime('now');
        
        $period = new AccompanyingPeriod($datetime1);
        $period->setDateClosing($datetime2);
        
        $this->assertFalse($period->isClosingAfterOpening());
    }
    
    public function testClosingEqualOpening() {
        $datetime = new \DateTime('now');
        
        $period = new AccompanyingPeriod($datetime);
        $period->setDateClosing($datetime);
        
        $this->assertTrue($period->isClosingAfterOpening());
    }
    
    public function testIsOpen() {
        $period = new AccompanyingPeriod(new \DateTime());
        
        $this->assertTrue($period->isOpen());
    }
    
    public function testIsClosed() {
        $period = new AccompanyingPeriod(new \DateTime());
        $period->setDateClosing(new \DateTime('tomorrow'));
        
        $this->assertFalse($period->isOpen());
    }
 
    /**
     * This test seems only to test ordering datetime... Maybe delete ?
     */
    public function testOrder() {
        $d = new \DateTime(); $d->setDate(2013, 2, 1);
        $g = new \DateTime(); $g->setDate(2013, 4, 1);
        $f = new \DateTime(); $f->setDate(2013, 1, 1);
        $e = new \DateTime(); $e->setDate(2013,3,1);
        
        $a = array($d, $g, $f, $e);
        
        usort($a, function($a, $b) {
            if ($a === $b) {
                return 0;
            }
            
            if ($a < $b) {
                return -1;
            } else {
                return 1;
            }
        });
        
        $date = $a[0]->format('Y-m-d');
        
        $this->assertEquals($date, '2013-01-01');
    }
}