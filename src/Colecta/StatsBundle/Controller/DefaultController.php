<?php

namespace Colecta\StatsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('StatsBundle:Default:index.html.twig', array('name' => $name));
    }
}
