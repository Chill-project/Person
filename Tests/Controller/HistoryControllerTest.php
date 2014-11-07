<?php

namespace Chill\PersonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Chill\PersonBundle\Entity\PersonHistoryFile;
use Chill\PersonBundle\Entity\Person;

class HistoryControllerTest extends WebTestCase
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
    
    public function testHistoryOrderWithUnorderedHistory() {
        $d = new \DateTime(); $d->setDate(2013, 2, 1);
        $p = new Person($d);
        
        $e = new \DateTime(); $e->setDate(2013,3,1);
        $p->close($e, null);
        
        $f = new \DateTime(); $f->setDate(2013, 1, 1);
        $p->open($f);
        
        $g = new \DateTime(); $g->setDate(2013, 4, 1); 
        $p->close($g, null);
        
        $r = $p->getHistoriesOrdered();
        
        $date = $r[0]->getDateOpening()->format('Y-m-d');
        
        
        $this->assertEquals($date, '2013-01-01');
    }
    
    
    public function testHistoryOrderSameDateOpening() {
        $d = new \DateTime(); $d->setDate(2013, 2, 1);
        $p = new Person($d);
        
        $e = new \DateTime(); $e->setDate(2013, 3, 1);
        $p->close($e, null);
        
        $f = new \DateTime(); $f->setDate(2013, 2, 1);
        $p->open($f);
        
        $g = new \DateTime(); $g->setDate(2013, 4, 1); 
        $p->close($g, null);
        
        $r = $p->getHistoriesOrdered();
        
        $date = $r[0]->getDateClosing()->format('Y-m-d');
        
        
        $this->assertEquals($date, '2013-03-01');
    }
    
    public function testDateCoveringWithCoveringHistory() {
        $d = new \DateTime(); $d->setDate(2013, 2, 1);
        $p = new Person($d);
        
        $e = new \DateTime(); $e->setDate(2013,3,1);
        $p->close($e, null);
        
        $f = new \DateTime(); $f->setDate(2013, 1, 1);
        $p->open($f);
        
        $g = new \DateTime(); $g->setDate(2013, 4, 1); 
        $p->close($g, null);
        
        $r = $p->checkHistoryIsNotCovering();
        
        $this->assertEquals($r['result'], Person::ERROR_OPENING_IS_INSIDE_CLOSING);
    }
    
    
    
    public function testNotOpenAFileReOpenedLater() {
        $d = new \DateTime(); $d->setDate(2013, 2, 1);
        $p = new Person($d);
        
        $e = new \DateTime(); $e->setDate(2013, 3, 1);
        $p->close($e, null);
        
        $f = new \DateTime(); $f->setDate(2013, 1, 1);
        $p->open($f);
        
       
        
        $r = $p->checkHistoryIsNotCovering();
        
        var_dump($r);
        
        $this->assertEquals($r['result'], Person::ERROR_OPENING_NOT_CLOSED_IS_BEFORE_NEW_LINE);
    }
    
    
    
    
    
    
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
