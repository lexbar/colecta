<?php

namespace Colecta\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function themeAction()
    {
        $twig_globals = $this->container->get('twig')->getGlobals();
        
        if(isset($twig_globals['web_theme']))
        {
            $response = $this->render('ColectaSiteBundle:Themes:'. $twig_globals['web_theme'] .'.css.twig');
        }
        else //default theme
        {
            $response = $this->render('ColectaSiteBundle:Default:classic.css.twig');
        }
        
        $response->headers->set('Content-Type', 'text/css');
        
        return $response;
    }
}
