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
 * Provide method to build timeline for accompanying periods
 * 
 * This class is resued by TimelineAccompanyingPeriodOpening (for opening)
 * and TimelineAccompanyingPeriodClosing (for closing)
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
abstract class AbstractTimelineAccompanyingPeriod implements TimelineProviderInterface
{
    /**
     *
     * @var EntityManager
     */
    protected $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getEntities(array $ids)
    {
        $periods = $this->em
                ->getRepository('ChillPersonBundle:AccompanyingPeriod')
                ->findBy(array('id' => $ids));
        
        //set results in an associative array with id as indexes
        $results = array();
        foreach($periods as $period) {
            $results[$period->getId()] = $period;
        }
        
        return $results;
    }

    /**
     * prepare fetchQuery without `WHERE` and `TYPE` clause
     * 
     * @param string $context
     * @param array $args
     * @return array
     * @throws \LogicException
     */
    protected function basicFetchQuery($context, array $args)
    {
        if ($context !== 'person') {
            throw new \LogicException('TimelineAccompanyingPeriod is not able '
                    . 'to render context '.$context);
        }
        
        $metadata = $this->em
                ->getClassMetadata('ChillPersonBundle:AccompanyingPeriod')
                ;
        
        return array(
            'id' => $metadata->getColumnName('id'),
            'date' => $metadata->getColumnName('date_opening'),
            'FROM' => $metadata->getTableName(),
        );
    }

    /**
     * return the expected response for TimelineProviderInterface::getEntityTemplate
     * 
     * @param string $template the template for rendering
     * @param mixed $entity
     * @param string $context
     * @param array $args
     * @return array
     */
    protected function getBasicEntityTemplate($template, $entity, $context, array $args)
    {
        return array(
            'template' => $template,
            'template_data' => ['person' => $args['person'], 'period' => $entity]
        );
    }
}
