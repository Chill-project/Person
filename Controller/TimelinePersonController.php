<?php

/*
 * Copyright (C) 2015 Champs-Libres Coopérative <info@champs-libres.coop>
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

namespace Chill\PersonBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 *
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class TimelinePersonController extends Controller
{

    public function personAction(Request $request, $person_id)
    {
        $person = $this->getDoctrine()
                ->getRepository('ChillPersonBundle:Person')
                ->find($person_id);

        if ($person === NULL) {
            throw $this->createNotFoundException();
        }

        return $this->render('ChillPersonBundle:Timeline:index.html.twig', array
            (
                'timeline' => $this->get('chill.main.timeline_builder')
                        ->getTimelineHTML('person', array('person' => $person)),
                'person' => $person
            )
        );
    }

}
