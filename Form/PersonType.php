<?php

namespace CL\Chill\PersonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CL\Chill\PersonBundle\Form\Type\CivilType;
use CL\Chill\PersonBundle\Form\Type\GenderType;
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
            ->add('name')
            ->add('surname')
            ->add('dateOfBirth', 'date', array('required' => false))
            ->add('placeOfBirth', 'text', array('required' => false))
            ->add('genre', new GenderType(), array(
                'required' => false
            ))
            ->add('civil_union', new CivilType(), array(
                'required' => false
            ))
            ->add('nbOfChild', 'integer', array('required' => false))
            ->add('belgian_national_number', new BelgianNationalNumberType(), 
                    array('required' => false))
            ->add('memo', 'text', array('required' => false))
            ->add('address', 'text',  array('required' => false))
            ->add('email', 'text', array('required' => false))
            ->add('countryOfBirth', 'entity', array(
                'required' => false,
                'class' => 'CL\Chill\MainBundle\Entity\Country'
                ))
            ->add('nationality', 'entity', array(
                'required' => false,
                'class' => 'CL\Chill\MainBundle\Entity\Country'
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CL\Chill\PersonBundle\Entity\Person'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cl_chill_personbundle_person';
    }
}
