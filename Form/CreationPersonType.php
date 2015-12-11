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

namespace Chill\PersonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Chill\PersonBundle\Form\Type\GenderType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class CreationPersonType extends AbstractType
{
    
    private $form_status;
    
    const NAME = 'chill_personbundle_person_creation';
    
    const FORM_NOT_REVIEWED = 'not_reviewed';
    const FORM_REVIEWED = 'reviewed' ;
    const FORM_BEING_REVIEWED = 'being_reviewed';
    
    public function __construct($form_status = self::FORM_NOT_REVIEWED) {
        $this->setStatus($form_status);
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->form_status === self::FORM_BEING_REVIEWED) {
            
            $dateToStringTransformer = new DateTimeToStringTransformer(
                    null, null, 'd-m-Y', false);
            
            $builder->add('firstName', 'hidden')
                    ->add('lastName', 'hidden')
                    ->add('birthdate', 'hidden', array(
                        'property_path' => 'birthdate'
                    ))
                    ->add('gender', 'hidden')
                    ->add('creation_date', 'hidden', array(
                        'mapped' => false
                    ))
                    ->add('form_status', 'hidden', array(
                        'mapped' => false
                    ))
                    ->add('center', 'center')
                    ;
            $builder->get('birthdate')
                    ->addModelTransformer($dateToStringTransformer);
            $builder->get('creation_date', 'hidden')
                    ->addModelTransformer($dateToStringTransformer);
        } else {
            $builder
                ->add('firstName')
                ->add('lastName')
                ->add('birthdate', 'date', array('required' => false, 
                    'widget' => 'single_text', 'format' => 'dd-MM-yyyy'))
                ->add('gender', new GenderType(), array(
                    'required' => true, 'empty_value' => null
                ))
                ->add('creation_date', 'date', array(
                    'required' => true, 
                    'widget' => 'single_text', 
                    'format' => 'dd-MM-yyyy',
                    'mapped' => false,
                    'data' => new \DateTime()))
                ->add('form_status', 'hidden', array(
                    'data' => $this->form_status,
                    'mapped' => false
                    ))
                ->add('center', 'center')
            ;
        }
    }
    
    private function setStatus($status) {
        $this->form_status = $status;
    }
      
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Chill\PersonBundle\Entity\Person'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
