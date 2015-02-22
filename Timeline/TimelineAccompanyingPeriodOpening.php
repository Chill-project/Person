<?php

/*
 * Copyright (C) 2015 Champs-Libres Coopérative <info@champs-libres.coop>
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

namespace Chill\PersonBundle\Timeline;

use Chill\MainBundle\Timeline\TimelineProviderInterface;
use Doctrine\ORM\EntityManager;

/**
 * Provide information for opening periods to timeline
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class TimelineAccompanyingPeriodOpening extends AbstractTimelineAccompanyingPeriod
{

    /**
     * 
     * {@inheritDoc}
     */
    public function supportsType($type)
    {
        return $type === 'accompanying_period_opening';
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function fetchQuery($context, array $args)
    {
        
        $data = $this->basicFetchQuery($context, $args);
        
        $data['type'] = 'accompanying_period_opening';
        $data['WHERE'] = sprintf('%s = %d', 
                $this->em
                    ->getClassMetadata('ChillPersonBundle:AccompanyingPeriod')
                    ->getAssociationMapping('person')['joinColumns'][0]['name'],
                $args['person']->getId());
        
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getEntityTemplate($entity, $context, array $args)
    {
        return $this->getBasicEntityTemplate(
                'ChillPersonBundle:Timeline:opening_period.html.twig', 
                $entity, 
                $context, 
                $args
                );
    }
}
