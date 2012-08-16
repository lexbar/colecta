<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('ColectaActivityBundle:Default:index.html.twig', array('name' => $name));
    }
}
