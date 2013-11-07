<?php

namespace CL\Chill\PersonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CL\Chill\PersonBundle\Entity\Person;
use CL\Chill\PersonBundle\Form\PersonType;
use Symfony\Component\HttpFoundation\Request;

class PersonController extends Controller {
    
    public function viewAction($id){
        
        $person = $this->_getPerson($id);
        
        if ($person === null) {
            return $this->createNotFoundException("Person with id $id not found on this server");
        }
        
        return $this->render('CLChillPersonBundle:Person:view.html.twig',
                array("person" => $person)
                );
        
    }
    
    public function editAction($id) {
        $person = $this->_getPerson($id);
        
        if ($person === null) {
            return $this->createNotFoundException();
        }
        
        $form = $this->createForm(new PersonType(), $person, array(
            'action' => $this->generateUrl('chill_person_general_update', array(
                'id' => $id
            ))
        ));
                
        
        
        return $this->render('CLChillPersonBundle:Person:edit.html.twig', 
                array('person' => $person, 
                    'form' => $form->createView()));
    }
    
    public function updateAction($id, Request $request) {
        $person = $this->_getPerson($id);
        
        if ($person === null) {
            return $this->createNotFoundException();
        }
        
        $form = $this->createForm(new PersonType(), $person);
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            
            if ( ! $form->isValid() ) {
                
                $errors = $form->getErrorsAsString();
                
                $this->get('session')
                        ->getFlashBag()->add('danger', 'error' . $errors);
                
                return $this->render('CLChillPersonBundle:Person:edit.html.twig', 
                        array('person' => $person, 
                            'form' => $form->createView()));
            }
            
            $this->get('session')->getFlashBag()
                    ->add('success', 
                            $this->get('translator')
                            ->trans('validation.Person.form.person.success')
                            );
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            $url = $this->generateUrl('chill_person_view', array( 
                'id' => $person->getId()
            ));
            
            return $this->redirect($url);
        }
    }
    
    public function searchAction() {
        $q = $this->getRequest()->query->getAlnum('q', '');
        $q = trim($q);
        
        if ( $q === '' ) {
            $this->get('session')
                    ->getFlashBag()
                    ->add('info', 
                            $this->get('translator')
                            ->trans('search.q_is_empty') );
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $offset = $this->getRequest()->query->getInt('offet', 0);
        $limit = $this->getRequest()->query->getInt('limit', 30);
        
        $persons = $em->createQuery('SELECT p FROM CLChillPersonBundle:Person p'
                . ' WHERE LOWER(p.name) like LOWER(:q) OR LOWER(p.surname) '
                . ' like LOWER(:q) ')
                ->setParameter('q', '%'.$q.'%')
              //  ->setOffset($offset)
              //  ->setLimit($limit)
                ->getResult()
                ;
        
        
        if (count($persons) === 0 ){
            $this->get('session')
                    ->getFlashBag()
                    ->add('info', 
                            $this->get('translator')
                            ->trans('search.no_results', array( 
                                '%q%' => $q
                            ))
                            );
        }
        
        return $this->render('CLChillPersonBundle:Person:list.html.twig', 
                array( 
                    'persons' => $persons,
                    'pattern' => $q
                ));
    }
    
    /**
     * easy getting a person by his id
     * @return \CL\Chill\PersonBundle\Entity\Person
     */
    private function _getPerson($id) {
        $em = $this->getDoctrine()->getManager();
        
        $person = $em->getRepository('CLChillPersonBundle:Person')
                ->find($id);
        
        return $person;
    }

}
