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

namespace Chill\PersonBundle\Test;

use Chill\PersonBundle\Entity\Person;
use Chill\MainBundle\Entity\Center;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
trait PreparePersonTrait
{
    
    /**
     * prepare a person
     * 
     * Properties added are : 
     * - firstname
     * - lastname
     * - gender
     * 
     * This person should not be persisted in a database
     * 
     * @param Center $center
     * @return Person
     */
    protected function preparePerson(Center $center)
    {
        return (new Person())
              ->setCenter($center)
              ->setFirstName('test firstname')
              ->setLastName('default lastname')
              ->setGender(Person::MALE_GENDER)
              ;
    }
    
}
