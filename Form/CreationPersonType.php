<?php

namespace CL\Chill\PersonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CL\Chill\PersonBundle\Form\Type\CivilType;
use CL\Chill\PersonBundle\Form\Type\GenderType;
use CL\BelgianNationalNumberBundle\Form\BelgianNationalNumberType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

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
            
            $dateToStringTransformer = new DateTimeToStringTransformer(
                    null, null, 'dd-MM-yyyy', true);
            
            
            $builder->add('name', 'hidden')
                    ->add('surname', 'hidden')
                    ->add( $builder->create('dateOfBirth', 'hidden')
                            ->addModelTransformer($dateToStringTransformer)
                          )
                    ->add('genre', 'hidden')
                    ->add( $builder->create('creation_date', 'hidden')
                            ->addModelTransformer($dateToStringTransformer)
                           )
                    ->add('form_status', 'hidden')
                    ;
        } else {
            $builder
                ->add('name')
                ->add('surname')
                ->add('dateOfBirth', 'date', array('required' => false, 
                    'widget' => 'single_text', 'format' => 'dd-MM-yyyy'))
                ->add('genre', new GenderType(), array(
                    'required' => true, 'empty_value' => null
                ))
                ->add('creation_date', 'date', array('required' => true, 
                    'widget' => 'single_text', 'format' => 'dd-MM-yyyy',
                    'data' => new \DateTime()))
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
