<?php

namespace CL\Chill\PersonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonHistoryFileType extends AbstractType
{
   
    private $motives = array();
    
    public function __construct(array $motivesArray) {
        foreach ($motivesArray as $key => $params ) {
            $this->motives[$key] = $params['label'];
        }
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date_opening', 'date', array("required" => true, 
                'widget' => 'single_text'))
            ->add('date_closing', 'date', array('required' => true,
                'widget' => 'single_text'))
            ->add('motive', 'choice', array(
                'choices' => $this->motives,
                'required' => true
            ))
            ->add('memo', 'textarea', array(
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
            'data_class' => 'CL\Chill\PersonBundle\Entity\PersonHistoryFile'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cl_chill_personbundle_personhistoryfile';
    }
}
