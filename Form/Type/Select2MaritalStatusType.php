<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\PersonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Chill\MainBundle\Form\Type\DataTransformer\ObjectToIdTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * A type to select the marital status
 *
 * @author Champs-Libres COOP
 */
class Select2MaritalStatusType extends AbstractType
{
    /** @var RequestStack */
    private $requestStack;

    /** @var ObjectManager */
    private $em;

    public function __construct(RequestStack $requestStack,ObjectManager $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }
    
    public function getName() {
        return 'select2_chill_marital_status';
    }
    
    public function getParent() {
        return 'select2_choice';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ObjectToIdTransformer($this->em,'Chill\PersonBundle\Entity\MaritalStatus');
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $maritalStatuses = $this->em->getRepository('Chill\PersonBundle\Entity\MaritalStatus')->findAll();
        $choices = array();

        foreach ($maritalStatuses as $ms) {
            $choices[$ms->getId()] = $ms->getName()[$locale];
        }

        asort($choices, SORT_STRING | SORT_FLAG_CASE);

        $resolver->setDefaults(array(
           'class' => 'Chill\PersonBundle\Entity\MaritalStatus',
           'choices' => $choices
        ));
    }
}
