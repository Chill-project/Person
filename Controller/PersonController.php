<?php

namespace CL\Chill\PersonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CL\Chill\PersonBundle\Entity\Person;

class PersonController extends Controller {
    
    public function viewAction($id){
        
        $person = $this->_getPerson($id);
        
        if ($person === null) {
            $this->createNotFoundException("Person with id $id not found on this server");
        }
        
        return $this->render('CLChillPersonBundle:Person:view.html.twig',
                array("person" => $person)
                );
        
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
                    'pattern' => $q,
                    'menu_composer' => $this->get('menu_composer')
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
