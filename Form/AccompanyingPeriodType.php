<?php

namespace Chill\PersonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class AccompanyingPeriodType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //if the period_action is close, date opening should not be seen
        if ($options['period_action'] !== 'close') {
            $builder
                ->add('openingDate', 'date', array(
                   "required" => true, 
                   'widget' => 'single_text',
                   'format' => 'dd-MM-yyyy'
                   ));
        }
        
        // the closingDate should be seen only if period_action = close 
        // or period_action = update AND accopanying period is already closed
        $builder->addEventListener(
              FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $accompanyingPeriod = $event->getData();
            $form = $event->getForm();

            //add date opening
            if (
                  //if the period_action is "close, should not be shown"
                  ($options['period_action'] === 'close')
                  OR
                  ($options['period_action'] === 'update' AND !$accompanyingPeriod->isOpen())
                  ) {
                $form->add('closingDate', 'date', array('required' => true,
                'widget' => 'single_text', 'format' => 'dd-MM-yyyy'));
                $form->add('closingMotive', 'closing_motive');
            }
        });
        
        $builder->add('remark', 'textarea', array(
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
            'data_class' => 'Chill\PersonBundle\Entity\AccompanyingPeriod'
        ));

        $resolver
            ->setRequired(array('period_action'))
            ->addAllowedTypes(array('period_action' => 'string'))
            ->addAllowedValues(array('period_action' => array(
                'update', 'open', 'close')));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'chill_personbundle_accompanyingperiod';
    }
}
