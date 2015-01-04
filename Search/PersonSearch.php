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
use Doctrine\ORM\EntityManagerInterface;
use Chill\PersonBundle\Entity\Person;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Chill\MainBundle\Search\ParsingException;

class PersonSearch extends AbstractSearch
{
    use ContainerAwareTrait;
    
    /**
     * 
     * @var EntityManagerInterface
     */
    private $em;
    
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
    
    public function supports($domain)
    {
        return 'person' === $domain;
    }

    /*
     * (non-PHPdoc)
     * @see \Chill\MainBundle\Search\SearchInterface::renderResult()
     */
    public function renderResult(array $terms, $start = 0, $limit = 50, array $options = array())
    {
        
        return $this->container->get('templating')->render('ChillPersonBundle:Person:list.html.twig', 
                array( 
                    'persons' => $this->search($terms, $start, $limit, $options),
                    'pattern' => 'trim($pattern)',
                    'total' => $this->count($terms)
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
    protected function search(array $terms, $start, $limit, array $options = array())
    {
        $qb = $this->createQuery($terms, 'search');
        
        $qb->select('p')
              ->setMaxResults($limit)
              ->setFirstResult($start);
        
        return $qb->getQuery()->getResult();
    }
    
    protected function count(array $terms)
    {
        $qb = $this->createQuery($terms);
        
        $qb->select('COUNT(p.id)');
        
        return $qb->getQuery()->getSingleScalarResult();
    }
    
    
    private $_cacheQuery = array();
    
    /**
     * 
     * @param array $terms
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQuery(array $terms) 
    {
        //get from cache
        $cacheKey = md5(serialize($terms));
        if (array_key_exists($cacheKey, $this->_cacheQuery)) {
            return $this->_cacheQuery[$cacheKey];
        }
        
        $qb = $this->em->createQueryBuilder();
        
        $qb->from('ChillPersonBundle:Person', 'p');
        
        if (array_key_exists('firstname', $terms)) {
            $qb->andWhere($qb->expr()->like('UNACCENT(LOWER(p.firstName))', ':firstname'))
                  ->setParameter('firstname', '%'.$terms['firstname'].'%');
        }
        
        if (array_key_exists('lastname', $terms)) {
            $qb->andWhere($qb->expr()->like('UNACCENT(LOWER(p.lastName))', ':lastname'))
                  ->setParameter('lastname', '%'.$terms['lastname'].'%');
        }
        
        if (array_key_exists('birthdate', $terms)) {
            try {
                $date = new \DateTime($terms['birthdate']);
            } catch (\Exception $ex) {
                throw new ParsingException('The date is '
                      . 'not parsable', 0, $ex);
            }

            $qb->andWhere($qb->expr()->eq('p.dateOfBirth', ':birthdate'))
              ->setParameter('birthdate', $date);
        }
        
        if (array_key_exists('gender', $terms)) {
            if (!in_array($terms['gender'], array(Person::GENRE_MAN, Person::GENRE_WOMAN))) {
                throw new ParsingException('The gender '
                      .$terms['gender'].' is not accepted. Should be "'.Person::GENRE_MAN
                      .'" or "'.Person::GENRE_WOMAN.'"');
            }
            
            $qb->andWhere($qb->expr()->eq('p.genre', ':gender'))
              ->setParameter('gender', $terms['gender']);
        }
        
        if (array_key_exists('nationality', $terms)) {
            try {
                $country = $this->em->createQuery('SELECT c FROM '
                      . 'ChillMainBundle:Country c WHERE '
                      . 'LOWER(c.countryCode) LIKE :code')
                      ->setParameter('code', $terms['nationality'])
                      ->getSingleResult();
            } catch (\Doctrine\ORM\NoResultException $ex)  {
                throw new ParsingException('The country code "'.$terms['nationality'].'" '
                      . ', used in nationality, is unknow', 0, $ex);
            }
            
            $qb->andWhere($qb->expr()->eq('p.nationality', ':nationality'))
                  ->setParameter('nationality', $country);
        }
        
        if ($terms['_default'] !== '') {
            $grams = explode(' ', $terms['_default']);
            
            foreach($grams as $key => $gram) {
                $qb->andWhere($qb->expr()
                      ->like('UNACCENT(LOWER(CONCAT(p.firstName, \' \', p.lastName)))', ':default_'.$key))
                      ->setParameter('default_'.$key, '%'.$gram.'%');
            }
        }
        
        $this->_cacheQuery[$cacheKey] = $qb;
        
        return $qb;
    }

}