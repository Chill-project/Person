<?php

namespace Chill\PersonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Chill\PersonBundle\Entity\Person;
use Chill\PersonBundle\Form\PersonHistoryFileType;
use Chill\PersonBundle\Entity\PersonHistoryFile;

class HistoryController extends Controller
{
    public function listAction($person_id){
        
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
        
        return $this->render('ChillPersonBundle:History:list.html.twig', 
                array('histories' => $person->getHistoriesOrdered(),
                    'person' => $person));
        
    }
    
    public function createAction($person_id) {
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
               
        $history = new PersonHistoryFile(new \DateTime());
        $history->setPerson($person)
              ->setDateOpening(new \DateTime())
              ->setDateClosing(new \DateTime());
        
        
        $form = $this->createForm(new PersonHistoryFileType(), 
                $history, array('period_action' => 'update'));
        
        $request = $this->getRequest();
        
        if ($request->getMethod() === 'POST') {
            
            $form->handleRequest($request);
            
            $errors = $this->_validatePerson($person);
            
            $flashBag = $this->get('session')->getFlashBag();
            
            if ($form->isValid(array('Default', 'closed')) 
                    && count($errors) === 0) {
                $em = $this->getDoctrine()->getManager();
                
                $em->persist($history);
                
                $em->flush();
                
                $flashBag->add('success', 
                        $this->get('translator')->trans(
                                'History created!'));
                
                return $this->redirect($this->generateUrl('chill_person_history_list',
                        array('person_id' => $person->getId())));
            } else {
                
                $flashBag->add('danger', $this->get('translator')
                        ->trans('Error! History not created!'));
                
                foreach($errors as $error) {
                    $flashBag->add('info', $error->getMessage());
                }
            }
        }
        
        
        
        return $this->render('ChillPersonBundle:History:form.html.twig',
                array(
                   'form' => $form->createView(),
                   'person' => $person,
                   'history' => $history
                   ) 
              );
    }

    public function updateAction($history_id){
        $em = $this->getDoctrine()->getManager();
        
        $history = $em->getRepository('ChillPersonBundle:PersonHistoryFile')
                ->find($history_id);
        
        if ($history === null) {
            return $this->createNotFoundException("history with id ".$history_id.
                    " is not found");
        }
        
        $person = $history->getPerson();
        
        $form = $this->createForm(new PersonHistoryFileType(), 
                $history, array('period_action' => 'update'));
        
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
                                'Updating history done'));
                
                return $this->redirect($this->generateUrl('chill_person_history_list',
                        array('person_id' => $person->getId())));
            } else {
                
                $flashBag->add('danger', $this->get('translator')
                        ->trans('Error when updating history'));
                
                foreach($errors as $error) {
                    $flashBag->add('info', $error->getMessage());
                }
            }
        }
        
        
        
        return $this->render('ChillPersonBundle:History:form.html.twig', 
                array(
                   'form' => $form->createView(),
                   'person' => $person,
                   'history' => $history
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
                            ->trans('Beware history is closed', 
                                    array('%name%' => $person->__toString())));
            
            return $this->redirect(
                    $this->generateUrl('chill_person_history_list', array(
                        'person_id' => $person->getId()
                )));
        }
        
        $current = $person->getCurrentHistory();   
        
        $form = $this->createForm(new PersonHistoryFileType(), $current, array(
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
                                    ->trans('History closed!', 
                                            array('%name%' => $person->__toString())));

                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirect(
                            $this->generateUrl('chill_person_history_list', array(
                                'person_id' => $person->getId()
                            ))
                            );
                } else {
                    $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('Error! History not closed!'));
                    
                    foreach ($errors as $error) {
                        $this->get('session')->getFlashBag()
                            ->add('info', $error->getMessage());
                    }
                    
                    


                }

            } else { //if form is not valid
                $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('History closing form is not valide'));
            }
        }
        
        
        
        
        
        return $this->render('ChillPersonBundle:History:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'person' => $person,
                    'history' => $current
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
        $errors_history = $this->get('validator')->validate($person, 
                            array('history_consistent'));
        
        foreach($errors_history as $error ) {
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
                            ->trans('Error! History %name% is not closed ; it can be open', 
                                    array('%name%' => $person->__toString())));
            
            return $this->redirect(
                    $this->generateUrl('chill_person_history_list', array(
                        'person_id' => $person->getId()
                )));
        }

        $history = new PersonHistoryFile(new \DateTime());

        $form = $this->createForm(new PersonHistoryFileType(), $history, array(
           'period_action' => 'open'));
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $person->open($history);
                
                $errors = $this->_validatePerson($person);

                if (count($errors) <= 0) {
                    $this->get('session')->getFlashBag()
                            ->add('success', $this->get('translator')
                                    ->trans('History %name% opened!', 
                                            array('%name%' => $person->__toString())));

                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirect(
                            $this->generateUrl('chill_person_history_list', array(
                                'person_id' => $person->getId()
                            ))
                            );
                } else {
                    $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('History not opened'));
                    
                    foreach ($errors as $error) {
                        $this->get('session')->getFlashBag()
                            ->add('info', $error->getMessage());
                    }
                }
            
            } else { // if errors in forms
                $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('History not opened : form is invalid'));
            }
        
        }
        
        return $this->render('ChillPersonBundle:History:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'person' => $person,
                    'history' => $history
                ));
    }
    
    private function _getPerson($id) {
        return $this->getDoctrine()->getManager()
                ->getRepository('ChillPersonBundle:Person')
                ->find($id);
    }

}
