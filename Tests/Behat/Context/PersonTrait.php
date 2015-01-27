<?php

/*
 * Chill is a suite of a modules, Chill is a software for social workers
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 * 
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\PersonBundle\Tests\Behat\Context;

use Chill\PersonBundle\Entity\Person;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\UnitOfWork;

/**
 * This trait helps to select "Person" entities in Features 
 * and manipulate pages or entities
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
trait PersonTrait
{
    
    use \Behat\Symfony2Extension\Context\KernelDictionary;
    
    /**
     *
     * @var int[]
     */
    private $peopleIds;
    
    /**
     * @var Chill\PersonBundle\Entity\Person
     */
    private $person = NULL;
    
    /**
     * Store a person to be retrieved later
     * 
     * @param Person $person
     */
    private function selectPerson(Person $person)
    {
        $this->person = $person;
    }
    
    /**
     * Return the current selected person
     * 
     * @return Person
     */
    public function getSelectedPerson()
    {
        return $this->person;
    }
    
    
    /**
     * Select person randomly.
     * 
     * 
     * @Given a random person is selected
     */
    public function selectRandomPerson()
    {
        //store the id in peopleIds (the ids are caches)
        //the retrieve only the selected person based on his id
        if ($this->peopleIds === NULL) {
            $this->peopleIds = $this->getContainer()->get('doctrine.orm.entity_manager')
                  ->createQuery("SELECT p.id FROM ChillPersonBundle:Person p")
                  ->getResult()
                    ;
        }
        
        $person = $this->getContainer()->get('doctrine.orm.entity_manager')
                ->getRepository('ChillPersonBundle:Person')
                ->find($this->peopleIds[rand(0, count($this->peopleIds) -1 )]);
        
        $this->selectPerson($person);
    }
    
    /**
     * @Given I am on landing person page for the selected person
     */
    public function goToLandingPageForSelectedPerson()
    {
        $this->getSession()->visit(sprintf('/en/person/%s/general', 
                $this->getSelectedPerson()->getId()));
    }
    
    /**
     * @Then the selected person value :key should be :value
     */
    public function assertPersonValueInDB($key, $value)
    {
        $person = $this->getSelectedPerson();
        
        //reload person from db
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        switch ($em->getUnitOfWork()->getEntityState($person))
        {
            case UnitOfWork::STATE_MANAGED:
                $em->refresh($person);
                break;
            case UnitOfWork::STATE_DETACHED:
                $person = $em
                    ->getRepository('ChillPersonBundle:Person')
                    ->find($person->getId());
                break;
            default:
                throw new \LogicException('the person is nor managed or detached');
        }
        
        $accessor = PropertyAccess::createPropertyAccessor();
        
        return $accessor->getValue($person, $key) == $value;
        
    }
}
