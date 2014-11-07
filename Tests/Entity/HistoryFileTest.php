<?php

namespace Chill\PersonBundle\Tests\Entity;

use Chill\PersonBundle\Entity\PersonHistoryFile;
use Chill\PersonBundle\Entity\Person;

class HistoryFileTest extends \PHPUnit_Framework_TestCase
{
    public function testClosingIsAfterOpeningConsistency()
    {
        $datetime1 = new \DateTime('now');
        
        $history = new PersonHistoryFile($datetime1);
        
        
        $datetime2 = new \DateTime('tomorrow');
        
        $history->setDateClosing($datetime2);
        
        
        $r = $history->isClosingAfterOpening();
        
        $this->assertTrue($r);
    }
    
    public function testClosingIsBeforeOpeningConsistency() {
        $datetime1 = new \DateTime('tomorrow');
        
        
        $history = new PersonHistoryFile($datetime1);
        
        
        $datetime2 = new \DateTime('now');
        
        $history->setDateClosing($datetime2);
        
        $this->assertFalse($history->isClosingAfterOpening());
    }
    
    public function testClosingEqualOpening() {
        $datetime = new \DateTime('now');
        
        $history = new PersonHistoryFile($datetime);
        $history->setDateClosing($datetime);
        
        $this->assertTrue($history->isClosingAfterOpening());
    }
    
    public function testIsOpen() {
        $history = new PersonHistoryFile(new \DateTime());
        
        $this->assertTrue($history->isOpen());
    }
    
    public function testIsClosed() {
        $history = new PersonHistoryFile(new \DateTime());
        
        $history->setDateClosing(new \DateTime('tomorrow'));
        
        $this->assertFalse($history->isOpen());
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
