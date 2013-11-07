<?php

namespace CL\Chill\PersonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CL\Chill\PersonBundle\Entity\Person;

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
        
        $rootTranslate = 'person.gender.';
        
        $a = array(
            Person::GENRE_MAN => $rootTranslate.Person::GENRE_MAN,
            Person::GENRE_WOMAN => $rootTranslate.Person::GENRE_WOMAN
        );
        
        $resolver->setDefaults(array( 
            'choices' => $a,
            'expanded' => true,
            'multiple' => false,
            'empty_value' => $rootTranslate.'undefined'
        ));
    }

}
