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

namespace Chill\PersonBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Chill\MainBundle\DataFixtures\ORM\LoadPermissionsGroup;
use Chill\MainBundle\Entity\RoleScope;

/**
 * Add a role CHILL_PERSON_UPDATE & CHILL_PERSON_CREATE for all groups except administrative,
 * and a role CHILL_PERSON_SEE for administrative
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadPersonACL extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 9600;
    }

    
    public function load(ObjectManager $manager)
    {
        foreach (LoadPermissionsGroup::$refs as $permissionsGroupRef) {
            $permissionsGroup = $this->getReference($permissionsGroupRef);
            
            //create permission group
            switch ($permissionsGroup->getName()) {
                case 'social':
                case 'direction':
                    printf("Adding CHILL_PERSON_UPDATE & CHILL_PERSON_CREATE to %s permission group \n", $permissionsGroup->getName());
                    $roleScopeUpdate = (new RoleScope())
                                ->setRole('CHILL_PERSON_UPDATE')
                                ->setScope(null);
                    $permissionsGroup->addRoleScope($roleScopeUpdate);
                    $roleScopeCreate = (new RoleScope())
                                ->setRole('CHILL_PERSON_CREATE')
                                ->setScope(null);
                    $permissionsGroup->addRoleScope($roleScopeCreate);
                    $manager->persist($roleScopeUpdate);
                    $manager->persist($roleScopeCreate);
                    break;
                case 'administrative':
                    printf("Adding CHILL_PERSON_SEE to %s permission group \n", $permissionsGroup->getName());
                    $roleScopeSee = (new RoleScope())
                                ->setRole('CHILL_PERSON_SEE')
                                ->setScope(null);
                    $permissionsGroup->addRoleScope($roleScopeSee);
                    $manager->persist($roleScopeSee);
                    break;
            }
            
        }
        
        $manager->flush();
    }

}
