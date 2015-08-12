<?php

/*
 * Chill is a software for social workers
 *
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

namespace Chill\PersonBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Chill\PersonBundle\Entity\MaritalStatus;

/**
 * Load marital status into database
 *
 * @author Marc Ducobu <marc@champs-libres.coop>
 */
class LoadMaritalStatus extends AbstractFixture implements OrderedFixtureInterface
{
    private $maritalStatuses = [
        ['id' => 'single', 'name' =>['en' => 'single', 'fr' => 'célibataire']],
        ['id' => 'married', 'name' =>['en' => 'married', 'fr' => 'marié(e)']],
        ['id' => 'widow', 'name' =>['en' => 'widow', 'fr' => 'veuf – veuve ']],
        ['id' => 'separat', 'name' =>['en' => 'separated', 'fr' => 'séparé(e)']],
        ['id' => 'divorce', 'name' =>['en' => 'divorced', 'fr' => 'divorcé(e)']],
        ['id' => 'legalco', 'name' =>['en' => 'legal cohabitant', 'fr' => 'cohabitant(e) légal(e)']],
        ['id' => 'unknown', 'name' =>['en' => 'unknown', 'fr' => 'indéterminé']]
    ];

    public function getOrder()
    {
        return 9999;
    }
    
    public function load(ObjectManager $manager)
    {
        echo "loading maritalStatuses... \n";

        foreach ($this->maritalStatuses as $ms) {
            echo $ms['name']['en'].' ';
            $new_ms = new MaritalStatus();
            $new_ms->setId($ms['id']);
            $new_ms->setName($ms['name']);
            $this->addReference('ms_'.$ms['id'], $new_ms);
            $manager->persist($new_ms);
        }
        
        $manager->flush();
    }
}
