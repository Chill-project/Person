<?php

/*
 *
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Chill\PersonBundle\Search;

use Chill\MainBundle\Search\AbstractSearch;
use Doctrine\Common\Persistence\ObjectManager;
use Chill\PersonBundle\Entity\Person;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class PersonSearch extends AbstractSearch
{
    use ContainerAwareTrait;
    
    /**
     * 
     * @var ObjectManager
     */
    private $om;
    
    
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /*
     * (non-PHPdoc)
     * @see \Chill\MainBundle\Search\SearchInterface::getLabel()
     */
    public function getLabel()
    {
        return 'person_default';
    }

    /*
     * (non-PHPdoc)
     * @see \Chill\MainBundle\Search\SearchInterface::getOrder()
     */
    public function getOrder()
    {
        return 100;
    }

    /*
     * (non-PHPdoc)
     * @see \Chill\MainBundle\Search\SearchInterface::isActiveByDefault()
     */
    public function isActiveByDefault()
    {
        return true;
    }

    /*
     * (non-PHPdoc)
     * @see \Chill\MainBundle\Search\SearchInterface::renderResult()
     */
    public function renderResult($pattern, $start = 0, $limit = 50, array $options = array())
    {
        
        $persons = $this->search($pattern, $start, $limit, $options);
        
        return $this->container->get('templating')->render('ChillPersonBundle:Person:list.html.twig', 
                array( 
                    'persons' => $persons,
                    'pattern' => trim($pattern),
                    'total' => count($persons)
                ));
    }
    
    /**
     * 
     * @param string $pattern
     * @param int $start
     * @param int $limit
     * @param array $options
     * @return Person[]
     */
    protected function search($pattern, $start, $limit, array $options = array())
    {
        $dql = 'SELECT p FROM ChillPersonBundle:Person p'
            . ' WHERE'
                . ' LOWER(p.firstName) like LOWER(:q)'
                    . ' OR LOWER(p.lastName)  like LOWER(:q)';
        
        $query = $this->om->createQuery($dql)
            ->setParameter('q', '%'.trim($pattern).'%')
            ->setFirstResult($start)
            ->setMaxResults($limit);
        
        return $query->getResult() ;
    }
}