<?php

/*
 * Copyright (C) 2015 Julien Fastré <julien.fastre@champs-libres.coop>
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

namespace Chill\PersonBundle\Tests\Validator;

use Chill\PersonBundle\Validator\Constraints\BirthdateValidator;
use Chill\PersonBundle\Validator\Constraints\Birthdate;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * Test the behaviour of BirthdayValidator
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class BirthdateValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * A prophecy for \Symfony\Component\Validator\Context\ExecutionContextInterface
     *
     * Will reveal \Symfony\Component\Validator\Context\ExecutionContextInterface
     */
    private $context;
    
    private $prophet;
    
    /**
     *
     * @var Birthdate
     */
    private $constraint;
    
    public function setUp()
    {
        $this->prophet = new Prophet;
        
        $constraintViolationBuilder = $this->prophet->prophesize();
        $constraintViolationBuilder->willImplement('Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface');

        $constraintViolationBuilder->setParameter(Argument::any(), Argument::any())
                ->willReturn($constraintViolationBuilder->reveal());
        $constraintViolationBuilder->addViolation()
                ->willReturn($constraintViolationBuilder->reveal());
        
        $this->context = $this->prophet->prophesize();
        $this->context->willImplement('Symfony\Component\Validator\Context\ExecutionContextInterface');
        $this->context->buildViolation(Argument::type('string'))
                ->willReturn($constraintViolationBuilder->reveal()); 
        
        $this->constraint = new Birthdate();
    }
    
    public function testValidBirthDate()
    {
        
        $date = new \DateTime('2015-01-01');
        
        $birthdateValidator = new BirthdateValidator();
        $birthdateValidator->initialize($this->context->reveal());
        
        $birthdateValidator->validate($date, $this->constraint);
        
        $this->context->buildViolation(Argument::any())->shouldNotHaveBeenCalled();
    }
    
    public function testInvalidBirthDate()
    {
        $date = new \DateTime('tomorrow');
        
        $birthdateValidator = new BirthdateValidator();
        $birthdateValidator->initialize($this->context->reveal());
        
        $birthdateValidator->validate($date, $this->constraint);
        
        $this->context->buildViolation(Argument::type('string'))->shouldHaveBeenCalled();
    }
    
    public function testInvalidBirthDateWithParameter()
    {
        $date = (new \DateTime('today'))->sub(new \DateInterval('P1M'));
        
        $birthdateValidator = new BirthdateValidator('P1Y');
        $birthdateValidator->initialize($this->context->reveal());
        
        $birthdateValidator->validate($date, $this->constraint);
        
        $this->context->buildViolation(Argument::type('string'))->shouldHaveBeenCalled();
    }
    
    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }
    
    
}
