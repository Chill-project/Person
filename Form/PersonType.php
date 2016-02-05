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

class PersonType extends AbstractType
{
    /**
     * array of configuration for person_fields.
     * 
     * Contains whether we should add fields some optional fields (optional per 
     * instance)
     *
     * @var string[]
     */
    protected $config = array();
    
    /**
     * 
     * @param string[] $personFieldsConfiguration configuration of visibility of some fields
     */
    public function __construct(array $personFieldsConfiguration)
    {
        $this->config = $personFieldsConfiguration;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('birthdate', 'date', array('required' => false, 'widget' => 'single_text', 'format' => 'dd-MM-yyyy'))
            ->add('gender', new GenderType(), array(
                'required' => true
            ))
            ->add('memo', 'textarea', array('required' => false))
            ;
        
        if ($this->config['place_of_birth'] === 'visible') {
            $builder->add('placeOfBirth', 'text', array('required' => false));
        }
        
        if ($this->config['phonenumber'] === 'visible') {
            $builder->add('phonenumber', 'textarea', array('required' => false));
        }
        
        if ($this->config['email'] === 'visible') {
            $builder->add('email', 'textarea', array('required' => false));
        }
            
        if ($this->config['country_of_birth'] === 'visible') {
            $builder->add('countryOfBirth', 'select2_chill_country', array(
                'required' => false
                ));
        }
            
        if ($this->config['nationality'] === 'visible') {
            $builder->add('nationality', 'select2_chill_country', array(
                'required' => false
                ));
        }
        
        if ($this->config['spoken_languages'] === 'visible') {
            $builder->add('spokenLanguages', 'select2_chill_language', array(
                'required' => false,
                'multiple' => true
                ));
        }
            
        if ($this->config['marital_status'] === 'visible'){
            $builder->add('maritalStatus', 'select2_chill_marital_status', array(
                'required' => false
                ));
        }

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
            'data_class' => 'Chill\PersonBundle\Entity\Person',
            'validation_groups' => array('general', 'creation')
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
