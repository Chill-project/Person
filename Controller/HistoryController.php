<?php

namespace CL\Chill\PersonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CL\Chill\PersonBundle\Entity\Person;

class HistoryController extends Controller
{
    public function listAction($id){
        
        $person = $this->_getPerson($id);
        
        if ($person === null) {
            return $this->createNotFoundException('Person not found');
        }
        
        return $this->render('CLChillPersonBundle:History:list.html.twig', 
                array('histories' => $person->getHistoriesOrdered(),
                    'person' => $person));
        
    }

    public function updateAction($historyId){
        
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
        
        
        
        
        
        return $this->render('CLChillPersonBundle:History:close.html.twig',
                array(
                    'form' => $form->createView(),
                    'person' => $person,
                    'history' => $current
                ));
    }
    
    /**
     * 
     * @param CL\Chill\PersonBundle\Entity\Person $person
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
        
        if ($person->isOpen() === true) {
            $this->get('session')->getFlashBag()
                    ->add('danger', $this->get('translator')
                            ->trans('controller.Person.history.open.is_not_closed', 
                                    array('%name%' => $person->__toString())));
            
            return $this->redirect(
                    $this->generateUrl('chill_person_history_list', array(
                        'id' => $person->getId()
                )));
        }
        
        $current = $person->getCurrentHistory();   
        
        $container = array(
            'dateOpening' => new \DateTime(),
            'texto' => null);
        
        
        
        $form = $this->createFormBuilder($container)
                ->add('dateOpening', 'date', array(
                    'data' => new \DateTime()
                ))
                ->add('texto', 'textarea', array(
                    'required' => false
                ))
                ->getForm();
        
        $request = $this->getRequest();
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
            
                $person->open($form['dateOpening']->getData(), 
                        $form['texto']->getData());
                
                
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
        
        
        
        
        
        return $this->render('CLChillPersonBundle:History:open.html.twig',
                array(
                    'form' => $form->createView(),
                    'person' => $person,
                    'history' => $current
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
                ->getRepository('CLChillPersonBundle:Person')
                ->find($id);
    }

}
