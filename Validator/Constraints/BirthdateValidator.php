<?php

/*
 * Copyright (C) 2015 Julien Fastré <julien.fastre@champs-libres.coop>
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

namespace Chill\PersonBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class BirthdateValidator extends ConstraintValidator
{
    private $interval_spec = null;
    
    public function __construct($interval_spec = null)
    {
        $this->interval_spec = $interval_spec;
    }
    
    public function validate($value, Constraint $constraint)
    {
        if ($value === NULL) {
            
            return;
        }
        
        if (!$value instanceof \DateTime) {
            throw new \LogicException('The input should a be a \DateTime interface,'
                    . (is_object($value) ? get_class($value) : gettype($value)));
        }
        
        $limitDate = $this->getLimitDate();
        
        if ($limitDate < $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%date%', $limitDate->format('d-m-Y'))
                ->addViolation();
        }
        
    }
    
    /**
     * 
     * @return \DateTime
     */
    private function getLimitDate()
    {
        if ($this->interval_spec !== NULL) {
            $interval = new \DateInterval($this->interval_spec);
            return (new \DateTime('now'))->sub($interval);
        } else {
            return (new \DateTime('now'));
        }
    }

}
