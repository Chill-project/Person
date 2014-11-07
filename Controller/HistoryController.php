<?php

namespace Chill\PersonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Chill\PersonBundle\Entity\Person;
use Chill\PersonBundle\Form\PersonHistoryFileType;
use Chill\PersonBundle\Entity\PersonHistoryFile;

class HistoryController extends Controller
{
    public function listAction($id){
        
        $person = $this->_getPerson($id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
        
        return $this->render('ChillPersonBundle:History:list.html.twig', 
                array('histories' => $person->getHistoriesOrdered(),
                    'person' => $person));
        
    }
    
    public function createAction($personId) {
        $person = $this->_getPerson($personId);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
               
        $history = new PersonHistoryFile(new \DateTime());
        $history->setPerson($person);
        
        $motivesArray = $this->get('service_container')
                ->getParameter('person.history.close.motives');
        
        $form = $this->createForm(new PersonHistoryFileType($motivesArray), 
                $history);
        
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
                                'controller.Person.history.create.done'));
                
                return $this->redirect($this->generateUrl('chill_person_history_list',
                        array('id' => $person->getId())));
            } else {
                
                $flashBag->add('danger', $this->get('translator')
                        ->trans('controller.Person.history.create.error'));
                
                foreach($errors as $error) {
                    $flashBag->add('info', $error->getMessage());
                }
            }
        }
        
        
        
        return $this->render('ChillPersonBundle:History:update.html.twig', 
                array('form' => $form->createView(),
                    'person' => $person ) );
    }

    public function updateAction($id, $historyId){
        $em = $this->getDoctrine()->getManager();
        
        $history = $em->getRepository('ChillPersonBundle:PersonHistoryFile')
                ->find($historyId);
        
        if ($history === null) {
            return $this->createNotFoundException("history with id ".$historyId.
                    " is not found");
        }
        
        if ($history->isOpen()) {
            $r = new \Symfony\Component\HttpFoundation\Response("You are not allowed "
                    . "to edit an opened history");
            $r->setStatusCode(400);
            return $r;
        }
        
        $person = $history->getPerson();
        
        if ($person->getId() != $id) {
            $r = new \Symfony\Component\HttpFoundation\Response("person id does not"
                    . " match history id");
            $r->setStatusCode(400);
            return $r;
        }
        
        $motivesArray = $this->get('service_container')
                ->getParameter('person.history.close.motives');
        
        $form = $this->createForm(new PersonHistoryFileType($motivesArray), 
                $history);
        
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
                                'controller.Person.history.update.done'));
                
                return $this->redirect($this->generateUrl('chill_person_history_list',
                        array('id' => $person->getId())));
            } else {
                
                $flashBag->add('danger', $this->get('translator')
                        ->trans('controller.Person.history.update.error'));
                
                foreach($errors as $error) {
                    $flashBag->add('info', $error->getMessage());
                }
            }
        }
        
        
        
        return $this->render('ChillPersonBundle:History:update.html.twig', 
                array('form' => $form->createView(),
                    'person' => $person ) );
    }
    
    public function closeAction($id) {
        $person = $this->_getPerson($id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
        
        if ($person->isOpen() === false) {
            $this->get('session')->getFlashBag()
                    ->add('danger', $this->get('translator')
                            ->trans('controller.Person.history.close.is_not_open', 
                                    array('%name%' => $person->__toString())));
            
            return $this->redirect(
                    $this->generateUrl('chill_person_history_list', array(
                        'id' => $person->getId()
                )));
        }
        
        $current = $person->getCurrentHistory();   
        
        $container = array(
            'dateClosing' => new \DateTime(),
            'motive' => null,
            'texto' => $current->getMemo());
        
        
        
        $form = $this->createFormBuilder($container)
                ->add('dateClosing', 'date', array(
                    'data' => new \DateTime()
                ))
                ->add('motive', 'choice', array(
                    'choices' => $this->_getMotive(),
                    'empty_value' => 'views.Person.close.select_a_motive'
                ))
                ->add('texto', 'textarea', array(
                    'required' => false
                ))
                ->getForm();
        
        $request = $this->getRequest();
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            
            if ($form->isValid()){
                


                $person->close($form['dateClosing']->getData(), 
                        $form['motive']->getData(),
                        $form['texto']->getData());
                
                
                $errors = $this->_validatePerson($person);
                
                

                if (count($errors) === 0) {
                    $this->get('session')->getFlashBag()
                            ->add('success', $this->get('translator')
                                    ->trans('controller.Person.history.close.done', 
                                            array('%name%' => $person->__toString())));

                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirect(
                            $this->generateUrl('chill_person_history_list', array(
                                'id' => $person->getId()
                            ))
                            );
                } else {
                    $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('controller.Person.history.close.error'));
                    
                    foreach ($errors as $error) {
                        $this->get('session')->getFlashBag()
                            ->add('info', $error->getMessage());
                    }
                    
                    


                }

            } else { //if form is not valid
                $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('controller.Person.history.close.error_in_form'));
            }
        }
        
        
        
        
        
        return $this->render('ChillPersonBundle:History:close.html.twig',
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
    
    
    public function openAction($id) {
        $person = $this->_getPerson($id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
        
        $request = $this->getRequest();
        
        if ($person->isOpen() === true && ! $request->query->has('historyId')) {
            $this->get('session')->getFlashBag()
                    ->add('danger', $this->get('translator')
                            ->trans('controller.Person.history.open.is_not_closed', 
                                    array('%name%' => $person->__toString())));
            
            return $this->redirect(
                    $this->generateUrl('chill_person_history_list', array(
                        'id' => $person->getId()
                )));
        }
        
        $historyId = $request->query->get('historyId', null);
        
        if ($historyId !== null) {
            $history = $this->getDoctrine()->getEntityManager()
                    ->getRepository('ChillPersonBundle:PersonHistoryFile')
                ->find($historyId);
            
        } else {
            $history = new PersonHistoryFile(new \DateTime());
        }
        
        //this may happen if we set an historyId and history is not closed
        if ($request->query->has('historyId') && ! $history->isOpen()) { 

            $r = new \Symfony\Component\HttpFoundation\Response("You are not allowed "
                    . "to edit a closed history");
            $r->setStatusCode(400);
            return $r;

        }
        

        
        
        
        $form = $this->createFormBuilder()
                ->add('dateOpening', 'date', array(
                    'data' => $history->getDateOpening()
                ))
                ->add('texto', 'textarea', array(
                    'required' => false,
                    'data' => $history->getMemo()
                ))
                ->getForm();
        
        
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                if ($request->query->has('historyId')) {
                    $history->setDateOpening($form['dateOpening']->getData())
                            ->setMemo($form['texto']->getData());
                } else {
                    $person->open($form['dateOpening']->getData(), 
                        $form['texto']->getData());
                }
                
                
                
                $errors = $this->_validatePerson($person);

                if (count($errors) <= 0) {
                    $this->get('session')->getFlashBag()
                            ->add('success', $this->get('translator')
                                    ->trans('controller.Person.history.open.done', 
                                            array('%name%' => $person->__toString())));

                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirect(
                            $this->generateUrl('chill_person_history_list', array(
                                'id' => $person->getId()
                            ))
                            );
                } else {
                    $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('controller.Person.history.open.error'));
                    
                    foreach ($errors as $error) {
                        $this->get('session')->getFlashBag()
                            ->add('info', $error->getMessage());
                    }
                }
            
            } else { // if errors in forms
                $this->get('session')->getFlashBag()
                            ->add('danger', $this->get('translator')
                                    ->trans('controller.Person.history.open.error_in_form'));
            }
        
        }
        
        
        
        
        
        return $this->render('ChillPersonBundle:History:open.html.twig',
                array(
                    'form' => $form->createView(),
                    'person' => $person,
                    'history' => $history
                ));
    }
    
    private function _getMotive() {
        $motivesArray = $this->get('service_container')
                ->getParameter('person.history.close.motives');
        
        $a = array();
        
        foreach ($motivesArray as $key => $params ) {
            $a[$key] = $params['label'];
        }
        
        return $a;
        
    }
    
    private function _getPerson($id) {
        return $this->getDoctrine()->getManager()
                ->getRepository('ChillPersonBundle:Person')
                ->find($id);
    }

}
