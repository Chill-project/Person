<?php

namespace Chill\PersonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A type to add a closing motive
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ClosingMotiveType extends AbstractType
{
    
    private $locale;
    
    public function __construct(Request $request = NULL)
    {
        if ($request !== NULL) {
            $this->locale = $request->getLocale();
        }
    }
    
    public function getName()
    {
        return 'closing_motive';
    }
    
    public function getParent()
    {
        return 'entity';
    }
    
    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        if ($this->locale === NULL) {
            throw new \LogicException('the locale should be defined and is extracted '
                  . 'from the \'request\' service. Maybe was this service '
                  . 'unaccessible ?');
        }
        
        $resolver->setDefaults(array(
                'class' => 'ChillPersonBundle:AccompanyingPeriod\ClosingMotive',
                'empty_data' => null,
                'empty_value' => 'Choose a motive',
                'property' => 'name['.$this->locale.']'
                )
            );
    }
    
}
