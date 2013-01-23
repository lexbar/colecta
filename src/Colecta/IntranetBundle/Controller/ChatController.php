<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\IntranetBundle\Entity\ChatInput;

class ChatController extends Controller
{
    
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $messages = $em->createQuery('SELECT i FROM ColectaIntranetBundle:ChatInput i ORDER BY i.date DESC')->setFirstResult(0)->setMaxResults(15)->getResult();
        
        return $this->render('ColectaIntranetBundle:Chat:index.html.twig', array('messages'=>$messages));
    }
    public function postAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
    
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        elseif(!$request->get('text'))
        {
            $this->get('session')->setFlash('error', 'No has escrito ningun texto');
        }
        else
        {
            $message = new ChatInput();
            $message->setText($request->get('text'));
            $message->setUser($user);
            $message->setDate(new \DateTime('now'));
            
            
            $em->persist($message); 
            $em->flush();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaPostIndex');
        }
        
        return new RedirectResponse($referer);
    }
}
