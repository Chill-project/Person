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
class CivilType extends AbstractType {
    
    
    public function getName() {
        return 'civil';
    }
    
    public function getParent() {
        return 'choice';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        
        $rootTranslate = 'person.civil_union.';
        
        $a = array(
            Person::CIVIL_COHAB => $rootTranslate.Person::CIVIL_COHAB,
            Person::CIVIL_DIVORCED => $rootTranslate.Person::CIVIL_DIVORCED,
            Person::CIVIL_SEPARATED => $rootTranslate.Person::CIVIL_SEPARATED,
            Person::CIVIL_SINGLE => $rootTranslate.Person::CIVIL_SINGLE,
            Person::CIVIL_UNKNOW => $rootTranslate.Person::CIVIL_UNKNOW,
            Person::CIVIL_WIDOW => $rootTranslate.Person::CIVIL_WIDOW
        );
        
        $resolver->setDefaults(array( 
            'choices' => $a,
            'expanded' => false,
            'multiple' => false,
            
        ));
    }

}
