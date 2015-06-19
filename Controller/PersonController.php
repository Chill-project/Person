<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2015, Champs Libres Cooperative SCRLFS, 
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
use Chill\PersonBundle\Form\PersonType;
use Chill\PersonBundle\Form\CreationPersonType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PersonController extends Controller
{
    public function getCFGroup()
    {
        $cFGroup = null;

        $em  = $this->getDoctrine()->getManager();
        $cFDefaultGroup  = $em->getRepository("ChillCustomFieldsBundle:CustomFieldsDefaultGroup")
            ->findOneByEntity("Chill\PersonBundle\Entity\Person");

        if($cFDefaultGroup) {
            $cFGroup = $cFDefaultGroup->getCustomFieldsGroup();
        }

        return $cFGroup;
    }

    public function viewAction($person_id)
    {    
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException("Person with id $person_id not"
                    . " found on this server");
        }
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_SEE', $person,
                "You are not allowed to see this person.");
        
        return $this->render('ChillPersonBundle:Person:view.html.twig',
            array("person" => $person,
                "cFGroup" => $this->getCFGroup()));
    }
    
    public function editAction($person_id)
    {
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException();
        }
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_UPDATE', $person, 
                'You are not allowed to edit this person');
        
        $form = $this->createForm(new PersonType(), $person,
            array(
                "action" => $this->generateUrl('chill_person_general_update',
                    array("person_id" => $person_id)),
                "cFGroup" => $this->getCFGroup()
            )
        );
                
        return $this->render('ChillPersonBundle:Person:edit.html.twig', 
            array('person' => $person, 'form' => $form->createView()));
    }
    
    public function updateAction($person_id, Request $request)
    {
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException();
        }
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_UPDATE', $person, 
                'You are not allowed to edit this person');
        
        $form = $this->createForm(new PersonType(), $person,
            array("cFGroup" => $this->getCFGroup()));
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ( ! $form->isValid() ) {                
                $this->get('session')
                        ->getFlashBag()->add('danger', 'Thp person data provided'
                              . ' are not valid');
                
                return $this->render('ChillPersonBundle:Person:edit.html.twig', 
                        array('person' => $person, 
                            'form' => $form->createView()));
            }
            
            $this->get('session')->getFlashBag()
                    ->add('success', 
                            $this->get('translator')
                            ->trans('The person data has been updated')
                            );
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            $url = $this->generateUrl('chill_person_view', array( 
                'person_id' => $person->getId()
            ));
            
            return $this->redirect($url);
        }
    }

    public function searchAction()
    {
        $q = $this->getRequest()->query->getAlnum('q', '');
        $q = trim($q);
        
        if ( $q === '' ) {
            $this->get('session')
                ->getFlashBag()
                ->add('info', 
                    $this->get('translator')
                    ->trans('Your query is empty. Be more explicive')
                );
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $offset = $this->getRequest()->query->getInt('offet', 0);
        $limit = $this->getRequest()->query->getInt('limit', 30);
        
        $dql = 'SELECT p FROM ChillPersonBundle:Person p'
                . ' WHERE'
                . ' LOWER(p.firstName) like LOWER(:q)'
                . ' OR LOWER(p.lastName)  like LOWER(:q)';
        
        if ($this->container->getParameter('cl_chill_person.search.use_double_metaphone')) {
            $dql .= ' OR DOUBLEMETAPHONE(p.lastName) = DOUBLEMETAPHONE(:qabsolute)';
        }

        
        $query = $em->createQuery($dql)
                ->setParameter('q', '%'.$q.'%');
        if ($this->container->getParameter('cl_chill_person.search.use_double_metaphone')) {
            $query->setParameter('qabsolute', $q);
        }
                
              //  ->setOffset($offset)
              //  ->setLimit($limit)
        $persons = $query->getResult() ;
        
        
        if (count($persons) === 0 ){
            $this->get('session')
                    ->getFlashBag()
                    ->add('info', 
                            $this->get('translator')
                            ->trans('Your query %q% gives no results', array( 
                                '%q%' => $q
                            ))
                            );
        }
        
        return $this->render('ChillPersonBundle:Person:list.html.twig', 
                array( 
                    'persons' => $persons,
                    'pattern' => $q
                ));
    }

    /**
     * Return a csv file with all the persons
     *
     * @return A csv file with all the persons
     */
    public function exportAction()
    {
        $em = $this->getDoctrine()->getManager();
        $persons = $em->getRepository('ChillPersonBundle:Person')->findAll();

        $response = $this->render('ChillPersonBundle:Person:export.csv.twig',
            array(
                'persons' => $persons,
                'cf_group' => $this->getCFGroup()));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        return $response;
    }
    
    public function newAction()
    {   
        // this is a dummy default center. 
        $defaultCenter = $this->get('security.token_storage')
                        ->getToken()
                        ->getUser()
                        ->getGroupCenters()[0]
                        ->getCenter();
        
        $person = (new Person())
                ->setCenter($defaultCenter);
        
        $form = $this->createForm(
                new CreationPersonType(CreationPersonType::FORM_NOT_REVIEWED), 
                array('creation_date' => new \DateTime(), 'center' => $defaultCenter),
                array('action' => $this->generateUrl('chill_person_review'))
                );
        
        return $this->_renderNewForm($form);   
    }
    
    private function _renderNewForm($form)
    {
        return $this->render('ChillPersonBundle:Person:create.html.twig', 
            array(
                'form' => $form->createView()
            ));
    }
    
    /**
     * 
     * @param type $form
     * @return \Chill\PersonBundle\Entity\Person
     */
    private function _bindCreationForm($form)
    {
        $date = new \DateTime();
        $person = new Person($form['creation_date']->getData());
        
        
        $date_of_birth = new \DateTime();
                
        $person->setFirstName($form['firstName']->getData())
                ->setLastName($form['lastName']->getData())
                ->setGenre($form['genre']->getData())
                ->setDateOfBirth($form['dateOfBirth']->getData())
                ->setCenter($form['center']->getData())
                ;
        
        return $person;
    }
    
    /**
     * 
     * @param \Chill\PersonBundle\Entity\Person $person
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    private function _validatePersonAndAccompanyingPeriod(Person $person)
    {
        $errors = $this->get('validator')
            ->validate($person, array('creation'));
        
        //validate accompanying periods
        $periods = $person->getAccompanyingPeriods();
        
        foreach ($periods as $period) {
            $period_errors = $this->get('validator')
                ->validate($period);
        
            //group errors :
            foreach($period_errors as $error) {
                $errors->add($error);
            }
        }
        
        return $errors;
    }
    
    public function reviewAction()
    {
        $request = $this->getRequest();
        
        if ($request->getMethod() !== 'POST') {
            $r = new Response("You must send something to review the creation of a new Person");
            $r->setStatusCode(400);
            return $r;
        }
        
        $form = $this->createForm(
                new CreationPersonType(CreationPersonType::FORM_BEING_REVIEWED),
                null, array('action' => $this->generateUrl('chill_person_create')));
        
        $form->handleRequest($request);
        
        $person = $this->_bindCreationForm($form);
        
        $errors = $this->_validatePersonAndAccompanyingPeriod($person);   
   
        if ( count($errors) > 0) {
            $flashBag = $this->get('session')->getFlashBag();
            $translator = $this->get('translator');
            
            $flashBag->add('danger', $translator->trans('The person data are not valid'));
            
            foreach($errors as $error) {
                $flashBag->add('info', $error->getMessage());
            }
            
            $form = $this->createForm(
                new CreationPersonType(CreationPersonType::FORM_NOT_REVIEWED),
                array('action' => $this->generateUrl('chill_person_create')));
        
            $form->handleRequest($request);
            
            return $this->_renderNewForm($form);
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery();
                
        $dql = 'SELECT p from ChillPersonBundle:Person p WHERE '
                . 'LOWER(p.firstName) LIKE LOWER(:firstName)'
                . ' OR LOWER(p.lastName) LIKE LOWER(:lastName)';

        $query->setParameter('firstName', $form['firstName']->getData())
                ->setParameter('lastName', $form['lastName']->getData());

        if ($this->container
                ->getParameter('cl_chill_person.search.use_double_metaphone')) {
            $dql .= ' OR DOUBLEMETAPHONE(p.lastName) LIKE DOUBLEMETAPHONE(:lastName)';
        }

        $query->setDql($dql);

        $alternatePersons = $query->getResult();

        if (count($alternatePersons) === 0) {
            return $this->forward('ChillPersonBundle:Person:create');
        }
        
        $this->get('session')->getFlashBag()->add('info', 
                $this->get('translator')->trans(
                        '%nb% person with similar name. Please verify that this is a new person', 
                        array('%nb%' => count($alternatePersons)))
                );
        
        return $this->render('ChillPersonBundle:Person:create_review.html.twig',
            array('alternatePersons' => $alternatePersons,
                'firstName' => $form['firstName']->getData(),
                'lastName' => $form['lastName']->getData(),
                'dateOfBirth' => $form['dateOfBirth']->getData(),
                'genre' => $form['genre']->getData(),
                'creation_date' => $form['creation_date']->getData(),
                'form' => $form->createView()));
    }
    
    public function createAction()
    {    
        $request = $this->getRequest();
        
        if ($request->getMethod() !== 'POST') {
            $r = new Response('You must send something to create a person !');
            $r->setStatusCode(400);
            return $r;
        }
        
        $form = $this->createForm(new CreationPersonType());
        
        $form->handleRequest($request);
        
        $person = $this->_bindCreationForm($form);
        
        $errors = $this->_validatePersonAndAccompanyingPeriod($person);
        
        $this->denyAccessUnlessGranted('CHILL_PERSON_CREATE', $person, 
                'You are not allowed to create this person');
        
        if ($errors->count() ===  0) {
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($person);
            
            $em->flush();
            
            return $this->redirect($this->generateUrl('chill_person_general_edit', 
                    array('person_id' => $person->getId())));
        } else {
            $r = new Response('this should not happen if you reviewed your submission');
            $r->setStatusCode(400);
            return $r;
        }
    }
    
    /**
     * easy getting a person by his id
     * @return \Chill\PersonBundle\Entity\Person
     */
    private function _getPerson($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $person = $em->getRepository('ChillPersonBundle:Person')
                ->find($id);
        
        return $person;
    }
}
