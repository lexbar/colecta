<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class VehicleController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('ColectaIntranetBundle:Vehicle:index.html.twig', array());
    }
}
