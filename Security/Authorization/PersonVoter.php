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

namespace Chill\PersonBundle\Security\Authorization;

use Chill\MainBundle\Security\Authorization\AbstractChillVoter;
use Chill\MainBundle\Entity\User;
use Chill\MainBundle\Security\Authorization\AuthorizationHelper;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PersonVoter extends AbstractChillVoter
{
    const CREATE = 'CHILL_PERSON_CREATE';
    const UPDATE = 'CHILL_PERSON_UPDATE';
    const SEE    = 'CHILL_PERSON_SEE';
    
    /**
     *
     * @var AuthorizationHelper
     */
    protected $helper;
    
    public function __construct(AuthorizationHelper $helper)
    {
        $this->helper = $helper;
    }
    
    protected function getSupportedAttributes()
    {
        return array(self::CREATE, self::UPDATE, self::SEE);
    }

    protected function getSupportedClasses()
    {
        return array('Chill\PersonBundle\Entity\Person');
    }

    protected function isGranted($attribute, $person, $user = null)
    {
        if (!$user instanceof User) {
            return false;
        }
        
        return $this->helper->userHasAccess($user, $person, $attribute);
        
    }
}
