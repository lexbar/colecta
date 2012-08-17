<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PostController extends Controller
{
    
    public function showAction($name)
    {
        return $this->render('ColectaItemBundle:Default:index.html.twig', array('name' => $name));
    }
}
