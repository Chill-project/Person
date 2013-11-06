<?php

namespace CL\Chill\PersonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('dateOfBirth')
            ->add('placeOfBirth')
            ->add('genre')
            ->add('civil_union')
            ->add('nbOfChild')
            ->add('belgian_national_number')
            ->add('memo')
            ->add('address')
            ->add('email')
            ->add('countryOfBirth')
            ->add('nationality')
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
