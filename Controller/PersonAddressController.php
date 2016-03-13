<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2016, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>, <info@champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Chill\PersonBundle\Entity\Person;
use Chill\MainBundle\Form\Type\AddressType;
use Chill\MainBundle\Entity\Address;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for addresses associated with person
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class PersonAddressController extends Controller
{
    
    public function listAction($person_id)
    {
        $person = $this->getDoctrine()->getManager()
              ->getRepository('ChillPersonBundle:Person')
              ->find($person_id);
        
        if ($person === NULL) {
            throw $this->createNotFoundException("Person with id $person_id not"
                    . " found ");
        }
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_SEE', $person,
                "You are not allowed to edit this person.");
        
        return $this->render('ChillPersonBundle:Address:list.html.twig', array(
            'person' => $person
        ));
    }
    
    public function newAction($person_id)
    {
        $person = $this->getDoctrine()->getManager()
              ->getRepository('ChillPersonBundle:Person')
              ->find($person_id);
        
        if ($person === NULL) {
            throw $this->createNotFoundException("Person with id $person_id not"
                    . " found ");
        }
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_UPDATE', $person,
                "You are not allowed to edit this person.");
        
        $address = new Address();
        
        $form = $this->createCreateForm($person, $address);
        
        return $this->render('ChillPersonBundle:Address:new.html.twig', array(
            'person' => $person,
            'form' => $form->createView()
        ));
    }
    
    public function createAction($person_id, Request $request)
    {
        $person = $this->getDoctrine()->getManager()
              ->getRepository('ChillPersonBundle:Person')
              ->find($person_id);
        
        if ($person === NULL) {
            throw $this->createNotFoundException("Person with id $person_id not"
                    . " found ");
        }
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_UPDATE', $person,
                "You are not allowed to edit this person.");
        
        $address = new Address();
        
        $form = $this->createCreateForm($person, $address);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();
            $person->addAddress($address);
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            $this->addFlash('success', 
                    $this->get('translator')->trans('The new address was created successfully')
                    );
            
            return $this->redirectToRoute('chill_person_address_list', array(
                'person_id' => $person->getId()
            ));
        }
        
        return $this->render('ChillPersonBundle:Address:new.html.twig', array(
            'person' => $person,
            'form' => $form->createView()
        ));
    }
    
    public function editAction($person_id, $address_id)
    {
        $person = $this->getDoctrine()->getManager()
              ->getRepository('ChillPersonBundle:Person')
              ->find($person_id);
        
        if ($person === NULL) {
            throw $this->createNotFoundException("Person with id $person_id not"
                    . " found ");
        }
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_UPDATE', $person,
                "You are not allowed to edit this person.");
        
        $address = $this->findAddressById($person, $address_id);
        
        $form = $this->createEditForm($person, $address);
        
        return $this->render('ChillPersonBundle:Address:edit.html.twig', array(
           'person' => $person,
           'address' => $address,
           'form' => $form->createView()
        ));
        
        
    }
    
    public function updateAction($person_id, $address_id, Request $request)
    {
        $person = $this->getDoctrine()->getManager()
              ->getRepository('ChillPersonBundle:Person')
              ->find($person_id);
        
        if ($person === NULL) {
            throw $this->createNotFoundException("Person with id $person_id not"
                    . " found ");
        }
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_UPDATE', $person,
                "You are not allowed to edit this person.");
        
        $address = $this->findAddressById($person, $address_id);
        
        $form = $this->createEditForm($person, $address);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()
                  ->flush();
            
            $this->addFlash('success', $this->get('translator')->trans(
                  "The address has been successfully updated"));
            
            return $this->redirectToRoute('chill_person_address_list', array(
               'person_id' => $person->getId()
            ));
        }
        
        return $this->render('ChillPersonBundle:Address:edit.html.twig', array(
           'person' => $person,
           'address' => $address,
           'form' => $form->createView()
        ));
    }
    
    /**
     * 
     * @param Person $person
     * @param Address $address
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditForm(Person $person, Address $address)
    {
        $form = $this->createForm(AddressType::class, $address, array(
           'method' => 'POST',
           'action' => $this->generateUrl('chill_person_address_update', array(
              'person_id' => $person->getId(),
              'address_id' => $address->getId()
           ))
        ));
        
        $form->add('submit', 'submit', array(
           'label' => 'Submit'
        ));
        
        return $form;
    }
    
    /**
     * 
     * @param Person $person
     * @param Address $address
     * @return \Symfony\Component\Form\Form
     */
    protected function createCreateForm(Person $person, Address $address)
    {
        $form = $this->createForm(AddressType::class, $address, array(
           'method' => 'POST',
           'action' => $this->generateUrl('chill_person_address_create', array(
              'person_id' => $person->getId()
           ))
        ));
        
        $form->add('submit', 'submit', array(
           'label' => 'Submit'
        ));
        
        return $form;
    }
    
    /**
     * 
     * @param Person $person
     * @param int $address_id
     * @return Address
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException if the address id does not exists or is not associated with given person
     */
    protected function findAddressById(Person $person, $address_id)
    {
        // filtering address
        $criteria = Criteria::create()
              ->where(Criteria::expr()->eq('id', $address_id))
              ->setMaxResults(1);
        $addresses = $person->getAddresses()->matching($criteria);
        
        if (count($addresses) === 0) {
            throw $this->createNotFoundException("Address with id $address_id "
                  . "matching person $person_id not found ");
        }
        
        return $addresses->first();
    }
}
