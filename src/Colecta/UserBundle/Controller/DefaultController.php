<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;


class DefaultController extends Controller
{
    
    public function loginAction()
    {
        $request = $this->getRequest(); 
        $session = $request->getSession();
        
        $error = $request->attributes->get( 
            SecurityContext::AUTHENTICATION_ERROR, 
            $session->get(SecurityContext::AUTHENTICATION_ERROR)
        );
        
        return $this->render('ColectaUserBundle:Default:login.html.twig', array( 
            'last_username' => $session->get(SecurityContext::LAST_USERNAME), 
            'error'	=>$error)
        );
    }
    
    public function resetPasswordAction()
    {
        $error = '';
        return $this->render('ColectaUserBundle:Default:resetPassword.html.twig', array('error'=>$error));
    }
}
