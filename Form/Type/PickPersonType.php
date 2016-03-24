<?php

/*
 * Copyright (C) 2016 Champs-Libres <info@champs-libres.coop>
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

namespace Chill\PersonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Chill\MainBundle\Entity\Center;
use Chill\PersonBundle\Entity\PersonRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Chill\MainBundle\Security\Authorization\AuthorizationHelper;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\Role;
use Chill\MainBundle\Entity\GroupCenter;
use Chill\PersonBundle\Entity\Person;

/**
 * This type allow to pick a person.
 * 
 * The form is embedded in a select2 input.
 * 
 * The people may be filtered : 
 * 
 * - with the `centers` option, only the people associated with the given center(s)
 * are seen. May be an instance of `Chill\MainBundle\Entity\Center`, or an array of 
 * `Chill\MainBundle\Entity\Center`. By default, all the reachable centers as selected.
 * - with the `role` option, only the people belonging to the reachable center for the
 * given role are displayed.
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PickPersonType extends AbstractType
{
    /**
     * @var PersonRepository
     */
    protected $personRepository;
    
    /**
     *
     * @var \Chill\MainBundle\Entity\User
     */
    protected $user;
    
    /**
     *
     * @var AuthorizationHelper
     */
    protected $authorizationHelper;
    
    public function __construct(
            PersonRepository $personRepository,
            TokenStorageInterface $tokenStorage,
            AuthorizationHelper $authorizationHelper
            )
    {
        $this->personRepository = $personRepository;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->authorizationHelper = $authorizationHelper;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $qb = $options['query_builder'];
        
        if ($options['role'] === NULL) {
            $centers = array_map(function (GroupCenter $g) { 
                
                return $g->getCenter(); 
            }, $this->user->getGroupCenters()->toArray());
        } else {
            $centers = $this->authorizationHelper
                    ->getReachableCenters($this->user, $options['role']);
        }
        
        if ($options['centers'] === NULL) {
            // we select all selected centers
            $selectedCenters = $centers;
        } else {
            $selectedCenters = array();
            $options['centers'] = is_array($options['centers']) ?
                    $options['centers'] : array($options['centers']);
            
            foreach ($options['centers'] as $c) {
                // check that every member of the array is a center
                if (!$c instanceof Center) { 
                    throw new \RuntimeException('Every member of the "centers" '
                            . 'option must be an instance of '.Center::class);
                }
                if (!in_array($c->getId(), array_map(
                        function(Center $c) { return $c->getId();},
                        $centers))) {
                    throw new AccessDeniedException('The given center is not reachable');
                }
                $selectedCenters[] = $c;
            }
        }
        
        
        $qb
                ->orderBy('p.firstName', 'ASC')
                ->orderBy('p.lastName', 'ASC')
                ->where($qb->expr()->in('p.center', ':centers'))
                ->setParameter('centers', $selectedCenters)
                ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        // add the possibles options for this type
        $resolver->setDefined('centers')
                ->addAllowedTypes('centers', array('array', Center::class, 'null'))
                ->setDefault('centers', null)
                ->setDefined('role')
                ->addAllowedTypes('role', array(Role::class, 'null'))
                ->setDefault('role', null)
                ;
        
        // add the default options
        $resolver->setDefaults(array(
            'class' => Person::class,
            'choice_label' => function(Person $p) {
                return $p->getFirstname().' '.$p->getLastname();
            },
            'placeholder' => 'Pick a person',
            'choice_attr' => function(Person $p) {
                return array(
                    'data-center' => $p->getCenter()->getId()
                );
            },
            'attr' => array('class' => 'select2 '),
            'query_builder' => $this->personRepository->createQueryBuilder('p')
        ));
    }
    
    public function getParent()
    {
        return \Symfony\Bridge\Doctrine\Form\Type\EntityType::class;
    }
    
}
