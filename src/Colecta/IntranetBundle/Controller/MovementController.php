<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class MovementController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('ColectaIntranetBundle:Movement:index.html.twig', array());
    }
}
