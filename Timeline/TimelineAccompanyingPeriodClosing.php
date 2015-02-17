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
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class TimelineAccompanyingPeriodOpening implements TimelineProviderInterface
{
    /**
     *
     * @var EntityManager
     */
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function fetchUnion($context, array $args)
    {
        if ($context !== 'person') {
            throw new \LogicException('TimelineAccompanyingPeriod is not able '
                    . 'to render context '.$context);
        }
        
        $idColumn =  $this->em
                ->getClassMetadata('ChillPersonBundle:AccompanyingPeriod')
                ->getColumnName('id');
        $dateColumn = $this->em
                ->getClassMetadata('ChillPersonBundle:AccompanyingPeriod')
                ->getColumnName('date_opening');
        $personForeignKeyColumn = $this->em
                ->getClassMetadata('ChillPersonBundle:AccompanyingPeriod')
                ->getAssociationMapping('person')['joinColumns'][0]['name'];
        $tableName = $this->em
                ->getClassMetadata('ChillPersonBundle:AccompanyingPeriod')
                ->getTableName();
        
        return sprintf("SELECT $idColumn AS id, 'accompanying_period_opening' AS key, "
                . "$dateColumn AS date "
                . "FROM $tableName "
                . "WHERE $personForeignKeyColumn = %d", 
                $args['person']->getId());
    }

    public function getEntities(array $ids)
    {
        $periods = $this->em
                ->getRepository('ChillPersonBundle:AccompanyingPeriod')
                ->findBy(array('id' => $ids));
        
        $results = array();
        foreach($periods as $period) {
            $results[$period->getId()] = array(
                'template' => 'ChillPersonBundle:Timeline:opening_period.html.twig',
                'entity' => array('period' => $period)
            );
        }
        
        return $results;
    }

    public function supportsKey($key)
    {
        return $key === 'accompanying_period_opening';
    }

}
