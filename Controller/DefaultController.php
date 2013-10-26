<?php

namespace CL\Chill\PersonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CLChillPersonBundle:Default:index.html.twig', array('name' => $name));
    }
}
