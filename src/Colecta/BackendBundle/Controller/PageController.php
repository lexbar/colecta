<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PageController extends Controller
{
    public function indexAction()
    {
        // SECURITY 
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigSettings())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        //END SECURITY
                
        if ($this->get('request')->getMethod() == 'POST') 
        {   
            $request = $this->get('request')->request;
                       
            if(0)
            {
                return new RedirectResponse($this->generateUrl('ColectaBackendSettingsIndex'));
            }
            
            if(1)
            {
                $this->get('session')->getFlashBag()->add('error', 'No se ha podido guardar correctamente la configuración.');
            }
        }
        
        return $this->render('ColectaBackendBundle:Page:index.html.twig', array('a'=>0));
    }
}