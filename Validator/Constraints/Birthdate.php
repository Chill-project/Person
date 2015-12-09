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

/**
 * Create a constraint on birth date: the birthdate after today are not allowed.
 * 
 * It is possible to add a delay before today, expressed as described in 
 * interval_spec : http://php.net/manual/en/dateinterval.construct.php
 * (this interval_spec itself is based on ISO8601 :
 *  https://en.wikipedia.org/wiki/ISO_8601#Durations)
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class Birthdate extends Constraint
{
    public $message = "The birthdate must be before %date%";
}
