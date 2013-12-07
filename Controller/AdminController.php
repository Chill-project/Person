<?php

namespace CL\Chill\PersonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * 
 *
 * @author julien.fastre@champs-libres.coop
 */
class AdminController extends Controller {
    
    public function indexAction() {
        return $this->forward('CLChillMainBundle:Admin:index',  
                array(
                    'menu' => 'admin_person',
                    'page_title' => 'menu.person.admin.index',
                    'header_title' => 'menu.person.header_index'
                    )
                );
    }
    
}
