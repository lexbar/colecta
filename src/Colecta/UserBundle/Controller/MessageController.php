<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContext;
use Colecta\UserBundle\Entity\Notification;
use Colecta\UserBundle\Entity\Message;


class MessageController extends Controller
{
    
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $messages = $em->getRepository('ColectaUserBundle:Message')->findBy(array('destination'=>$user->getId()), array('date'=>'DESC'),30,0);
        
        if(count($messages))
        {
            for($i = 0; $i < count($messages); $i++)
            {
                if(!$messages[$i]->getDismiss())
                {
                    $messages[$i]->setDismiss(true);
                    $em->persist($messages[$i]);
                    
                    $em->flush();
                    
                    $this->get('session')->setFlash('message'.$messages[$i]->getId() , 'unread');
                }
            }
        }
        
        return $this->render('ColectaUserBundle:Message:index.html.twig', array('messages' => $messages));
    }
    
    public function sentAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $messages = $em->getRepository('ColectaUserBundle:Message')->findBy(array('origin'=>$user->getId()), array('date'=>'DESC'),30,0);
        
        return $this->render('ColectaUserBundle:Message:sent.html.twig', array('messages' => $messages));
    }
    
    public function newAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request');
        
        if($user == 'anon.') 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        if ($request->getMethod() == 'POST') 
        {
            $persist = true;
            
            $destination = $request->request->get('destination');
            $text = $request->request->get('text');
            
            if(empty($destination))
            {
                $this->get('session')->setFlash('MessageDestinationError',true);
                $this->get('session')->setFlash('error', 'Debes indicar un destinatario');
                $persist = false;
            }
            
            $destinationUser = $em->getRepository('ColectaUserBundle:User')->findOneByName($destination);
            
            if(!$destinationUser)
            {
                $this->get('session')->setFlash('MessageDestinationError',true);
                $this->get('session')->setFlash('error', 'No existe el usuario en la base de datos');
                $persist = false;
            }
            
            if(empty($text))
            {
                $this->get('session')->setFlash('MessageTextError',true);
                $this->get('session')->setFlash('error', 'No puedes dejar el texto vacío');
                $persist = false;
            }
            
            if($persist)
            {
                $message = new Message();
                
                $message->setDate(new \DateTime('now'));
                $message->setText($text);
                $message->setResponseto(null);
                $message->setOrigin($user);
                $message->setDestination($destinationUser);
                $message->setDismiss(false);
                
                $em->persist($message); 
                $em->flush();
                
                //Send mail notification
                
                $mailer = $this->get('mailer');
                $configmail = $this->container->getParameter('mail');
                
                $message = \Swift_Message::newInstance();
			    $message->setSubject('Mensaje de '.$user->getName())
			        ->setFrom($configmail['from'])
			        ->setTo($destinationUser->getMail())
			        ->setBody($this->renderView('ColectaUserBundle:Message:mailAlert.txt.twig', array('from'=>$user, 'to'=>$destinationUser)), 'text/plain');
			    $mailer->send($message);
                
                $this->get('session')->setFlash('success', 'Mensaje enviado.');
                
                return new RedirectResponse($this->generateUrl('userSentMessages'));
            }
            else
            {
                $this->get('session')->setFlash('MessageDestination',$destination);
                $this->get('session')->setFlash('MessageText',$text);
                
                return $this->render('ColectaUserBundle:Message:new.html.twig');
            }
        }
        else
        {
            return $this->render('ColectaUserBundle:Message:new.html.twig');
        }
    }
    public function newToAction($user_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userTo = $em->getRepository('ColectaUserBundle:User')->find($user_id);
        
        if($user == 'anon.') 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        elseif(!$userTo)
        {
            return $this->render('ColectaUserBundle:Message:new.html.twig');
        }
        
        return $this->render('ColectaUserBundle:Message:new.html.twig', array('userTo'=>$userTo));
    }
    public function responseAction($responseto)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request');
        
        if($user == 'anon.') 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $responsetoMessage = $em->getRepository('ColectaUserBundle:Message')->findOneById($responseto);
        
        if(!$responsetoMessage)
        {
            $this->get('session')->setFlash('error', 'No se ha encontrado la conversación');
            return new RedirectResponse($this->generateUrl('userMessages'));
        }
        
        if ($request->getMethod() == 'POST') 
        {
            $persist = true;
            
            $text = $request->request->get('text');
            $destinationUser = $responsetoMessage->getOrigin();
            
            if(!$destinationUser)
            {
                $this->get('session')->setFlash('MessageDestinationError',true);
                $this->get('session')->setFlash('error', 'No existe el usuario en la base de datos');
                $persist = false;
            }
            
            if(empty($text))
            {
                $this->get('session')->setFlash('MessageTextError',true);
                $this->get('session')->setFlash('error', 'No puedes dejar el texto vacío');
                $persist = false;
            }
            
            if($persist)
            {
                $message = new Message();
                
                $message->setDate(new \DateTime('now'));
                $message->setText($text);
                $message->setResponseto($responsetoMessage);
                $message->setOrigin($user);
                $message->setDestination($destinationUser);
                $message->setDismiss(false);
                
                $em->persist($message); 
                $em->flush();
                
                //Send mail notification
                
                $mailer = $this->get('mailer');
                $configmail = $this->container->getParameter('mail');
                
                $message = \Swift_Message::newInstance();
			    $message->setSubject('Respuesta de '.$user->getName())
			        ->setFrom($configmail['from'])
			        ->setTo($destinationUser->getMail())
			        ->setBody($this->renderView('ColectaUserBundle:Message:mailAlert.txt.twig', array('from'=>$user, 'to'=>$destinationUser)), 'text/plain');
			    $mailer->send($message);
                
                $this->get('session')->setFlash('success', 'Mensaje enviado.');
                
                return new RedirectResponse($this->generateUrl('userMessages'));
            }
            else
            {
                $this->get('session')->setFlash('MessageDestination',$destination);
                $this->get('session')->setFlash('MessageText',$text);
                
                return $this->render('ColectaUserBundle:Message:response.html.twig', array('originalMessage'=>$responsetoMessage, 'destination'=>$responsetoMessage->getOrigin()->getName()));
            }
        }
        else
        {
            return $this->render('ColectaUserBundle:Message:response.html.twig', array('originalMessage'=>$responsetoMessage, 'destination'=>$responsetoMessage->getOrigin()->getName()));
        }
    }
}
