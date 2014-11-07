<?php

namespace Chill\PersonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Chill\PersonBundle\Entity\Person;
use Chill\PersonBundle\Form\PersonType;
use Chill\PersonBundle\Form\CreationPersonType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PersonController extends Controller {
    
    public function viewAction($person_id){
        
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException("Person with id $person_id not found on this server");
        }
        
        return $this->render('ChillPersonBundle:Person:view.html.twig',
                array("person" => $person)
                );
        
    }
    
    public function editAction($person_id) {
        $person = $this->_getPerson($person_id);
        
        if ($person === null) {
            return $this->createNotFoundException();
        }
        
        $form = $this->createForm(new PersonType(), $person, array(
            'action' => $this->generateUrl('chill_person_general_update', array(
                'person_id' => $person_id
            ))
        ));
                
        
        
        return $this->render('ChillPersonBundle:Person:edit.html.twig', 
                array('person' => $person, 
                    'form' => $form->createView()));
    }
    
    public function updateAction($person_id, Request $request) {
        $person = $this->_getPerson($person_id);
        
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
                
                return $this->render('ChillPersonBundle:Person:edit.html.twig', 
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
                'person_id' => $person->getId()
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
        
        $dql = 'SELECT p FROM ChillPersonBundle:Person p'
                . ' WHERE'
                . ' LOWER(p.name) like LOWER(:q)'
                . ' OR LOWER(p.surname)  like LOWER(:q)';
        
        if ($this->container->getParameter('cl_chill_person.search.use_double_metaphone')) {
            $dql .= ' OR DOUBLEMETAPHONE(p.name) = DOUBLEMETAPHONE(:qabsolute)';
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
                            ->trans('search.no_results', array( 
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
    
    public function newAction() {
        
        $form = $this->createForm(
                new CreationPersonType(CreationPersonType::FORM_NOT_REVIEWED), 
                null, array('action' => $this->generateUrl('chill_person_review')));
        
        return $this->_renderNewForm($form);
        
        
    }
    
    private function _renderNewForm($form) {
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
    private function _bindCreationForm($form) {
        $date = new \DateTime();
        $person = new Person($form['creation_date']->getData());
        
        
        $date_of_birth = new \DateTime();
                
        $person->setName($form['name']->getData())
                ->setSurname($form['surname']->getData())
                ->setGenre($form['genre']->getData())
                ->setDateOfBirth($form['dateOfBirth']->getData())
                ;
        
        return $person;
    }
    
    /**
     * 
     * @param \Chill\PersonBundle\Entity\Person $person
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    private function _validatePersonAndHistory(Person $person) {
        $errors = $this->get('validator')
                ->validate($person, array('creation'));
        
        //validate history
        $histories = $person->getHistories();
        
        foreach ($histories as $history) {
            $errors_history = $this->get('validator')
                ->validate($history);
        
            //group errors :
            foreach($errors_history as $error) {
                $errors->add($error);
            }
        }
        
        return $errors;
    }
    
    public function reviewAction() {
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
        
        $errors = $this->_validatePersonAndHistory($person);   
        
        
   
        if ( count($errors) > 0) {
            
            $flashBag = $this->get('session')->getFlashBag();
            $translator = $this->get('translator');
            
            $flashBag->add('danger', $translator->trans('controller.Person.review.problem_with_data'));
            
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
                . 'LOWER(p.name) LIKE LOWER(:name)'
                . ' OR LOWER(p.surname) LIKE LOWER(:surname)';

        $query->setParameter('name', $form['name']->getData())
                ->setParameter('surname', $form['surname']->getData());

        if ($this->container
                ->getParameter('cl_chill_person.search.use_double_metaphone')) {
            $dql .= ' OR DOUBLEMETAPHONE(p.name) LIKE DOUBLEMETAPHONE(:name)';
        }

        $query->setDql($dql);

        $alternatePersons = $query->getResult();

        if (count($alternatePersons) === 0) {
            return $this->forward('ChillPersonBundle:Person:create');
        }
        
        $this->get('session')->getFlashBag()->add('info', 
                $this->get('translator')->trans(
                        'controller.Person.review.people_with_similar_name', 
                        array('%nb%' => count($alternatePersons)))
                );
        
        return $this->render('ChillPersonBundle:Person:create_review.html.twig',
                array('alternatePersons' => $alternatePersons,
                    'name' => $form['name']->getData(),
                    'surname' => $form['surname']->getData(),
                    'dateOfBirth' => $form['dateOfBirth']->getData(),
                    'genre' => $form['genre']->getData(),
                    'creation_date' => $form['creation_date']->getData(),
                    'form' => $form->createView()));
        
    }
    
    public function createAction() {
        
        $request = $this->getRequest();
        
        if ($request->getMethod() !== 'POST') {
            $r = new Response('You must send something to create a person !');
            $r->setStatusCode(400);
            return $r;
        }
        
        $form = $this->createForm(new CreationPersonType());
        
        $form->handleRequest($request);
        
        $person = $this->_bindCreationForm($form);
        
        $errors = $this->_validatePersonAndHistory($person);
        
        if ($errors->count() ===  0) {
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($person);
            
            $em->flush();
            
            return $this->redirect($this->generateUrl('chill_person_view', 
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
    private function _getPerson($id) {
        $em = $this->getDoctrine()->getManager();
        
        $person = $em->getRepository('ChillPersonBundle:Person')
                ->find($id);
        
        return $person;
    }

}
