<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Colecta\IntranetBundle\Entity\ChatInput;
use Colecta\IntranetBundle\Entity\ChatInteraction;

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
            
            $this->recordInteraction();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaPostIndex');
        }
        
        return new RedirectResponse($referer);
    }
    public function reloadAction($lastinput)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            return false;
        }
        
        $this->recordInteraction();
        
        //Who is online
        $users = $em->createQuery('SELECT u FROM ColectaIntranetBundle:ChatInteraction i, ColectaUserBundle:User u WHERE i.user = u AND i.date > :date')->setParameter('date',new \DateTime('now -20 seconds'))->getResult();
        
        //Last inputs
        $inputs = $em->createQuery('SELECT i FROM ColectaIntranetBundle:ChatInput i WHERE i.id > :id')->setParameter('id',$lastinput)->getResult();
        
        $response = new Response($this->renderView('ColectaIntranetBundle:Chat:reload.json.twig', array('users' => $users, 'inputs' => $inputs)),200);
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    public function recordInteraction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            return false;
        }
        
        $interaction = $em->getRepository('ColectaIntranetBundle:ChatInteraction')->findOneByUser($user);
        
        if(!$interaction)
        {
            $interaction = new ChatInteraction();
            $interaction->setUser($user);
        }
        
        $interaction->setDate(new \DateTime('now'));
        $em->persist($interaction); 
        $em->flush();
        
        return $interaction;
    }
}
