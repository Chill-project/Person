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
use Chill\MainBundle\Entity\User;
use Chill\MainBundle\Entity\PermissionsGroup;
use Chill\MainBundle\Entity\GroupCenter;
use Chill\MainBundle\Entity\RoleScope;
use Chill\MainBundle\Entity\Scope;

/**
 * Test PersonVoter
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class PersonVoterTest extends KernelTestCase
{
    
    use PrepareUserTrait, PrepareCenterTrait, PrepareScopeTrait {
            PrepareUserTrait::setUpTrait insteadof PrepareCenterTrait, PrepareScopeTrait;
            PrepareUserTrait::tearDownTrait insteadof PrepareCenterTrait, PrepareScopeTrait;
        }
    
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
        $this->setUpTrait();
        $this->voter = static::$kernel->getContainer()
              ->get('chill.person.security.authorization.person');
    }
    
    public function testNullUser()
    {
        $token = $this->prepareToken();
        $center = $this->prepareCenter(1, 'center');
        $person = $this->preparePerson($center);
        
        $this->assertEquals(
                VoterInterface::ACCESS_DENIED,
                $this->voter->vote($token, $person, array('CHILL_PERSON_SEE')), 
                "assert that a null user is not allowed to see"
                );
    }
    
    public function testUserCanNotReachCenter()
    {
        $centerA = $this->prepareCenter(1, 'centera');
        $centerB = $this->prepareCenter(2, 'centerb');
        $token = $this->prepareToken(array(
            array(
                'center' => $centerA, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'CHILL_PERSON_UPDATE']
                )
            )
        ));
        $person = $this->preparePerson($centerB);
        
        $this->assertEquals(
                VoterInterface::ACCESS_DENIED,
                $this->voter->vote($token, $person, array('CHILL_PERSON_UPDATE')),
                'assert that a user with right not in the good center has access denied'
                );
    }
    
    /**
     * test a user with sufficient right may see the person
     */
    public function testUserAllowed()
    {
        $center = $this->prepareCenter(1, 'center');
        $scope = $this->prepareScope(1, 'default');
        $token = $this->prepareToken(array(
            array(
                'center' => $center, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'CHILL_PERSON_SEE']
                )
            )
        ));
        $person = $this->preparePerson($center);
        
        $this->assertEquals(
                VoterInterface::ACCESS_GRANTED,
                $this->voter->vote($token, $person, array('CHILL_PERSON_SEE')),
                'assert that a user with correct rights may is granted access'
                );
    }
    
    /**
     * test a user with sufficient right may see the person.
     * hierarchy between role is required
     */
    public function testUserAllowedWithInheritance()
    {
        $this->markTestAsSkipped();
    }    
    
    /**
     * prepare a person
     * 
     * The only properties set is the center, others properties are ignored.
     * 
     * @param Center $center
     * @return Person
     */
    protected function preparePerson(Center $center)
    {
        return (new Person())
              ->setCenter($center)
              ;
    }
    
    /**
     * prepare a token interface with correct rights
     * 
     * if $permissions = null, user will be null (no user associated with token
     * 
     * @param array $permissions an array of permissions, with key 'center' for the center and 'permissions' for an array of permissions
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    protected function prepareToken(array $permissions = null)
    {        
        $token = $this->prophet->prophesize();
        $token
            ->willImplement('\Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        if ($permissions === NULL) {
            $token->getUser()->willReturn(null);
        } else {
            $token->getUser()->willReturn($this->prepareUser($permissions));
        }
        
        return $token->reveal();
    }
}
