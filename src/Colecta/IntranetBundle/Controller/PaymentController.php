<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PaymentController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('ColectaIntranetBundle:Payment:index.html.twig', array());
    }
}
