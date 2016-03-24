<?php

/*
 * Copyright (C) 2016 Champs-Libres <info@champs-libres.coop>
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

namespace Chill\PersonBundle\Tests\Form\Type;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Chill\PersonBundle\Form\Type\PickPersonType;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PickPersonTypeTest extends KernelTestCase
{
    /**
     *
     * @var \Chill\MainBundle\Entity\User
     */
    protected $user;
    
    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    
    /**
     *
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;
    
    public function setUp()
    {
        self::bootKernel();
        
        $this->container = self::$kernel->getContainer();
        
        $this->user = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:User')
                ->findOneBy(array('username' => 'multi_center'));
        
        $this->formFactory = $this->container->get('form.factory');
        
        $token = (new UsernamePasswordToken($this->user, 'password', 'firewall'));
        $this->container->get('security.token_storage')
                ->setToken($token);
    }
    
    public function testWithoutOption()
    {
        $form = $this->formFactory
                ->createBuilder(PickPersonType::class, null, array())
                ->getForm();
        
        $this->assertInstanceOf(\Symfony\Component\Form\FormInterface::class,
                $form);
        
        // transform into a view to have data-center attr
        $view = $form->createView();
        
        $centerIds = array();
        
        /* @var $centerIds \Symfony\Component\Form\ChoiceList\View\ChoiceView */
        foreach($view->vars['choices'] as $choice) {
            $centerIds[] = $choice->attr['data-center'];
        }
        
        $this->assertEquals(2, count(array_unique($centerIds)),
                "test that the form contains people from 2 centers");
    }
    
    /**
     * Test the form with an option 'centers' with an unique center 
     * entity (not in an array)
     */
    public function testWithOptionCenter()
    {
        $center = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:Center')
                ->findOneBy(array('name' => 'Center A'))
                ;
        
        $form = $this->formFactory
                ->createBuilder(PickPersonType::class, null, array(
                    'centers' => $center
                ))
                ->getForm();
        
        // transform into a view to have data-center attr
        $view = $form->createView();
        
        /* @var $centerIds \Symfony\Component\Form\ChoiceList\View\ChoiceView */
        foreach($view->vars['choices'] as $choice) {
            $centerIds[] = $choice->attr['data-center'];
        }
        
        $this->assertEquals(1, count(array_unique($centerIds)),
                "test that the form contains people from only one centers");
        
        $this->assertEquals($center->getId(), array_unique($centerIds)[0]);
        
    }
    
    /**
     * Test the form with multiple centers
     */
    public function testWithOptionCenters()
    {
        $centers = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:Center')
                ->findAll()
                ;
        
        $form = $this->formFactory
                ->createBuilder(PickPersonType::class, null, array(
                    'centers' => $centers
                ))
                ->getForm();
        
        // transform into a view to have data-center attr
        $view = $form->createView();
        
        /* @var $centerIds \Symfony\Component\Form\ChoiceList\View\ChoiceView */
        foreach($view->vars['choices'] as $choice) {
            $centerIds[] = $choice->attr['data-center'];
        }
        
        $this->assertEquals(2, count(array_unique($centerIds)),
                "test that the form contains people from only one centers");
        
    }
    
    /**
     * test with an invalid center type in the option 'centers' (in an array)
     * 
     * @expectedException \RuntimeException
     */
    public function testWithInvalidOptionCenters()
    {
        
        $form = $this->formFactory
                ->createBuilder(PickPersonType::class, null, array(
                    'centers' => array('string')
                ))
                ->getForm();
    }
    
    public function testWithOptionRoleInvalid()
    {
        $form = $this->formFactory
                ->createBuilder(PickPersonType::class, null, array(
                    'role' => new \Symfony\Component\Security\Core\Role\Role('INVALID')
                ))
                ->getForm();
        
        // transform into a view to have data-center attr
        $view = $form->createView();
        
        $this->assertEquals(0, count($view->vars['choices']));
    }
    
}
