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

namespace Chill\PersonBundle\Timeline;

use Chill\MainBundle\Timeline\TimelineProviderInterface;
use Doctrine\ORM\EntityManager;

/**
 * Provide information for opening periods to timeline
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class TimelineAccompanyingPeriodClosing extends AbstractTimelineAccompanyingPeriod
{

    /**
     * 
     * {@inheritDoc}
     */
    public function supportsType($type)
    {
        return $type === 'accompanying_period_closing';
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function fetchQuery($context, array $args)
    {
        $metadata = $this->em
                    ->getClassMetadata('ChillPersonBundle:AccompanyingPeriod');
        
        $data = $this->basicFetchQuery($context, $args);
        
        $data['type'] = 'accompanying_period_closing';
        $data['WHERE'] = sprintf('%s = %d AND %s IS NOT NULL', 
                $metadata
                    ->getAssociationMapping('person')['joinColumns'][0]['name'],
                $args['person']->getId(),
                $metadata
                    ->getColumnName('date_closing')
                );
        
        return $data;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getEntityTemplate($entity, $context, array $args)
    {
        return $this->getBasicEntityTemplate(
                'ChillPersonBundle:Timeline:closing_period.html.twig', 
                $entity, 
                $context, 
                $args
                );
    }
}
