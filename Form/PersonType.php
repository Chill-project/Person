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
use Chill\PersonBundle\Form\Type\CivilType;
use Chill\PersonBundle\Form\Type\GenderType;
use CL\BelgianNationalNumberBundle\Form\BelgianNationalNumberType;

class PersonType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('dateOfBirth', 'date', array('required' => false, 'widget' => 'single_text', 'format' => 'dd-MM-yyyy'))
            ->add('placeOfBirth', 'text', array('required' => false))
            ->add('genre', new GenderType(), array(
                'required' => false
            ))
            ->add('memo', 'textarea', array('required' => false))
            ->add('phonenumber', 'textarea', array('required' => false))
            ->add('email', 'textarea', array('required' => false))
            ->add('countryOfBirth', 'select2_chill_country', array(
                'required' => false
                ))
            ->add('nationality', 'select2_chill_country', array(
                'required' => false
                ))
        ;

        if($options['cFGroup']) {
            $builder
                ->add('cFData', 'custom_field',
                array('attr' => array('class' => 'cf-fields'), 'group' => $options['cFGroup']))
            ;
        }

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Chill\PersonBundle\Entity\Person'
        ));

        $resolver->setRequired(array(
            'cFGroup'
        ));

        $resolver->setAllowedTypes(array(
            'cFGroup' => array('null', 'Chill\CustomFieldsBundle\Entity\CustomFieldsGroup')
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'chill_personbundle_person';
    }
}
