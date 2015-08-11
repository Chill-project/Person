<?php

namespace Chill\PersonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Chill\PersonBundle\Entity\Person;

/**
 * A type to select the civil union state
 *
 * @author julien
 */
class GenderType extends AbstractType {
    
    
    public function getName() {
        return 'gender';
    }
    
    public function getParent() {
        return 'choice';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        
        $a = array(
            Person::MALE_GENDER => Person::MALE_GENDER,
            Person::FEMALE_GENDER => Person::FEMALE_GENDER
        );
        
        $resolver->setDefaults(array( 
            'choices' => $a,
            'expanded' => true,
            'multiple' => false,
            'empty_value' => null
        ));
    }

}
