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

namespace Chill\PersonBundle\Tests\Security\Authorization;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Chill\PersonBundle\Entity\Person;
use Chill\MainBundle\Entity\Center;

/**
 * Test PersonVoter
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class PersonVoterTest extends KernelTestCase
{
    /**
     *
     * @var \Chill\PersonBundle\Security\Authorization\PersonVoter
     */
    protected $voter;
    
    /**
     *
     * @var \Prophecy\Prophet
     */
    protected $prophet;
    
    public function setUp()
    {
        static::bootKernel();
        $this->voter = static::$kernel->getContainer()
              ->get('chill.person.security.authorization.person');
        
        $this->prophet = new \Prophecy\Prophet;
    }
    
    public function testNullUser()
    {
        $token = $this->prepareToken();
        $center = $this->prepareCenter(1, 'center');
        $person = $this->preparePerson($center);
        
        $this->assertEquals(
              VoterInterface::ACCESS_DENIED,
              $this->voter->vote($token, $person, array('CHILL_PERSON_SEE')), 
              "assert that a null user is not allowed to see");
    }
    
    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
    
    
    protected function preparePerson(Center $center)
    {
        return (new Person())
              ->setCenter($center)
              ;
    }
    
    /**
     * prepare a mocked center, with and id and name given
     * 
     * @param int $id
     * @param string $name
     * @return \Chill\MainBundle\Entity\Center 
     */
    protected function prepareCenter($id, $name)
    {
        $center = $this->prophet->prophesize();
        $center->willExtend('\Chill\MainBundle\Entity\Center');
        $center->getId()->willReturn($id);
        $center->getName()->willReturn($name);
        
        return $center->reveal();
    }
    
    protected function prepareToken($username = null)
    {
        $token = $this->prophet->prophesize();
        $token
            ->willImplement('\Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->getUser()->willReturn(null);
        
        return $token->reveal();
    }
}
