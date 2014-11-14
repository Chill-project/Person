<?php

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
            ->add('name')
            ->add('surname')
            ->add('dateOfBirth', 'date', array('required' => false, 'widget' => 'single_text', 'format' => 'dd-MM-yyyy'))
            ->add('placeOfBirth', 'text', array('required' => false))
            ->add('genre', new GenderType(), array(
                'required' => false
            ))
            ->add('memo', 'textarea', array('required' => false))
            ->add('email', 'textarea', array('required' => false))
            ->add('countryOfBirth', 'select2_chill_country', array(
                'required' => false
                ))
            ->add('nationality', 'select2_chill_country', array(
                'required' => false
                ))
        ;
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
        return 'chill_personbundle_person';
    }
}
