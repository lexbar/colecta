<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class LotteryController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('ColectaIntranetBundle:Lottery:index.html.twig', array());
    }
}
