<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>
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
use Chill\PersonBundle\Form\AccompanyingPeriodType;
use Chill\PersonBundle\Entity\AccompanyingPeriod;

class AccompanyingPeriodController extends Controller
{
    public function listAction($person_id){
        
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
        
        return $this->render('ChillPersonBundle:AccompanyingPeriod:list.html.twig', 
                array('accompanying_periods' => $person->getAccompanyingPeriodsOrdered(),
                    'person' => $person));
        
    }
    
    public function createAction($person_id) {
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
               
        $accompanyingPeriod = new AccompanyingPeriod(new \DateTime());
        $accompanyingPeriod->setPerson($person)
              ->setDateOpening(new \DateTime())
              ->setDateClosing(new \DateTime());
        
        
        $form = $this->createForm(new AccompanyingPeriodType(), 
                $accompanyingPeriod, array('period_action' => 'update'));
        
        $request = $this->getRequest();
        
        if ($request->getMethod() === 'POST') {
            
            $form->handleRequest($request);
            
            $errors = $this->_validatePerson($person);
            
            $flashBag = $this->get('session')->getFlashBag();
            
            if ($form->isValid(array('Default', 'closed')) 
                    && count($errors) === 0) {
                $em = $this->getDoctrine()->getManager();
                
                $em->persist($accompanyingPeriod);
                
                $em->flush();
                
                $flashBag->add('success', 
                        $this->get('translator')->trans(
                                'Period created!'));
                
                return $this->redirect($this->generateUrl('chill_person_accompanying_period_list',
                        array('person_id' => $person->getId())));
            } else {
                
                $flashBag->add('danger', $this->get('translator')
                        ->trans('Error! Period not created!'));
                
                foreach($errors as $error) {
                    $flashBag->add('info', $error->getMessage());
                }
            }
        }
        
        
        
        return $this->render('ChillPersonBundle:AccompanyingPeriod:form.html.twig',
                array(
                   'form' => $form->createView(),
                   'person' => $person,
                   'accompanying_period' => $accompanyingPeriod
                   ) 
              );
    }

    public function updateAction($person_id, $period_id){
        $em = $this->getDoctrine()->getManager();
        
        $accompanyingPeriod = $em->getRepository('ChillPersonBundle:AccompanyingPeriod')
                ->find($period_id);
        
        if ($accompanyingPeriod === null) {
            return $this->createNotFoundException("Period with id ".$period_id.
                    " is not found");
        }
        
        $person = $accompanyingPeriod->getPerson();
        
        $form = $this->createForm(new AccompanyingPeriodType(), 
                $accompanyingPeriod, array('period_action' => 'update'));
        
        $request = $this->getRequest();
        
        if ($request->getMethod() === 'POST') {
            
            $form->handleRequest($request);
            
            $errors = $this->_validatePerson($person);
            
            $flashBag = $this->get('session')->getFlashBag();
            
            if ($form->isValid(array('Default', 'closed')) 
                    && count($errors) === 0) {
                $em->flush();
                
                $flashBag->add('success', 
                        $this->get('translator')->trans(
                                'Period updating done'));
                
                return $this->redirect($this->generateUrl('chill_person_accompanying_period_list',
                        array('person_id' => $person->getId())));
            } else {
                
                $flashBag->add('danger', $this->get('translator')
                        ->trans('Error when updating the period'));
                
                foreach($errors as $error) {
                    $flashBag->add('info', $error->getMessage());
                }
            }
        }
        
        
        
        return $this->render('ChillPersonBundle:AccompanyingPeriod:form.html.twig', 
                array(
                   'form' => $form->createView(),
                   'person' => $person,
                   'accompanying_period' => $accompanyingPeriod
              ) );
    }
    
    public function closeAction($person_id) {
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
        
        if ($person->isOpen() === false) {
            $this->get('session')->getFlashBag()
                    ->add('danger', $this->get('translator')
                            ->trans('Beware period is closed', 
                                    array('%name%' => $person->__toString())));
            
            return $this->redirect(
                    $this->generateUrl('chill_person_accompanying_period_list', array(
                        'person_id' => $person->getId()
                )));
        }
        
        $current = $person->getCurrentAccompanyingPeriod();   
        
        $form = $this->createForm(new AccompanyingPeriodType(), $current, array(
           'period_action' => 'close'
        ));

        $request = $this->getRequest();
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            
            if ($form->isValid()){
                $person->close($current);
                $errors = $this->_validatePerson($person);

                if (count($errors) === 0) {
                    $this->get('session')->getFlashBag()
                            ->add('success', $this->get('translator')
                                    ->trans('Period closed!', 
                                            array('%name%' => $person->__toString())));

                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirect(
                            $this->generateUrl('chill_person_accompanying_period_list', array(
                                'person_id' => $person->getId()
                            ))
                            );
                } else {
                    $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('Error! Period not closed!'));
                    
                    foreach ($errors as $error) {
                        $this->get('session')->getFlashBag()
                            ->add('info', $error->getMessage());
                    }
                }
            } else { //if form is not valid
                $this->get('session')->getFlashBag()
                    ->add('danger',
                        $this->get('translator')
                            ->trans('Pediod closing form is not valide')
                    );
                
                foreach ($form->getErrors() as $error) {
                    $this->get('session')->getFlashBag()
                        ->add('info', $error->getMessage());
                }
            }
        }
        
        return $this->render('ChillPersonBundle:AccompanyingPeriod:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'person' => $person,
                    'accompanying_period' => $current
                ));
    }
    
    /**
     * 
     * @param Chill\PersonBundle\Entity\Person $person
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    private function _validatePerson(Person $person) {
        $errors = $this->get('validator')->validate($person, 
                             array('Default'));
        $errors_accompanying_period = $this->get('validator')->validate($person, 
                            array('accompanying_period_consistent'));
        
        foreach($errors_accompanying_period as $error ) {
                    $errors->add($error);
                }
                
        return $errors;
    }
    
    
    public function openAction($person_id) {
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
        
        $request = $this->getRequest();
        
        //in case the person is already open
        if ($person->isOpen() === true) {
            $this->get('session')->getFlashBag()
                    ->add('danger', $this->get('translator')
                            ->trans('Error! Period %name% is not closed ; it can be open', 
                                    array('%name%' => $person->__toString())));
            
            return $this->redirect(
                    $this->generateUrl('chill_person_accompanying_period_list', array(
                        'person_id' => $person->getId()
                )));
        }

        $accompanyingPeriod = new AccompanyingPeriod(new \DateTime());

        $form = $this->createForm(new AccompanyingPeriodType(),
            $accompanyingPeriod, array('period_action' => 'open'));
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $person->open($accompanyingPeriod);
                
                $errors = $this->_validatePerson($person);

                if (count($errors) <= 0) {
                    $this->get('session')->getFlashBag()
                            ->add('success', $this->get('translator')
                                    ->trans('Period %name% opened!', 
                                            array('%name%' => $person->__toString())));

                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirect(
                            $this->generateUrl('chill_person_accompanying_period_list', array(
                                'person_id' => $person->getId()
                            ))
                            );
                } else {
                    $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('Period not opened'));
                    
                    foreach ($errors as $error) {
                        $this->get('session')->getFlashBag()
                            ->add('info', $error->getMessage());
                    }
                }
            
            } else { // if errors in forms
                $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('Period not opened : form is invalid'));
            }
        }
        
        return $this->render('ChillPersonBundle:AccompanyingPeriod:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'person' => $person,
                    'accompanying_period' => $accompanyingPeriod
                ));
    }
    
    private function _getPerson($id) {
        return $this->getDoctrine()->getManager()
                ->getRepository('ChillPersonBundle:Person')
                ->find($id);
    }

}
