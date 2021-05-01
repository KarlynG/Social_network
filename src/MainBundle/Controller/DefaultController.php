<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user_repo = $em->getRepository("MainBundle:User");
        
        $user = $user_repo->find(1);
        echo "Bienvenido ".$user->getName()." ".$user->getSurname();
        
        die();
        
        
        return $this->render('MainBundle:Default:index.html.twig');
    }
}
