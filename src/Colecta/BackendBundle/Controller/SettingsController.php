<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingsController extends Controller
{
    public function indexAction()
    {
        return $this->render('ColectaBackendBundle:Settings:index.html.twig');
    }
}
