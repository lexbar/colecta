<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;


class MessageController extends Controller
{
    
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            return new RedirectResponse($this->generateUrl('UserLogin'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $messages = $em->getRepository('ColectaUserBundle:Message')->findBy(array('destination'=>$user->getId()), array('date'=>'DESC'),30,0);
        
        return $this->render('ColectaUserBundle:Message:index.html.twig', array('messages' => $messages));
    }
    
    public function newAction()
    {
        return $this->render('ColectaUserBundle:Message:new.html.twig');
    }
}
