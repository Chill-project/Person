<?php

namespace CL\Chill\PersonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CL\Chill\PersonBundle\Form\Type\CivilType;
use CL\Chill\PersonBundle\Form\Type\GenderType;
use CL\BelgianNationalNumberBundle\Form\BelgianNationalNumberType;

class CreationPersonType extends AbstractType
{
    
    private $form_status;
    
    const NAME = 'cl_chill_personbundle_person_creation';
    
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
            $builder->add('name', 'hidden')
                    ->add('surname', 'hidden')
                    ->add('dateOfBirth', 'hidden')
                    ->add('genre', 'hidden')
                    ->add('creation_date', 'hidden')
                    ->add('form_status', 'hidden')
                    ;
        } else {
            $builder
                ->add('name')
                ->add('surname')
                ->add('dateOfBirth', 'date', array('required' => false, 'widget' => 'single_text'))
                ->add('genre', new GenderType(), array(
                    'required' => false
                ))
                ->add('creation_date', 'date', array('required' => true, 
                    'widget' => 'single_text', 'data' => new \DateTime()))
                ->add('form_status', 'hidden', array('data' => $this->form_status))
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
//        $resolver->setDefaults(array(
//            'data_class' => 'CL\Chill\PersonBundle\Entity\Person'
//        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
