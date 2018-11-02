<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Colecta\UserBundle\Entity\Notification;
use Colecta\UserBundle\Entity\Message;


class MessageController extends Controller
{
    
    public function indexAction()
    {
        $user = $this->getUser();
        
        if(!$user)
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        
        $sql = "SELECT m.id, m.responseto_id, m.user_origin_id, m.date, m.text, m.dismiss, u.id AS destination_id, u.name, u.role_id, u.profile_id, u.mail, u.avatar " .
        "FROM Message m INNER JOIN User u ON m.user_destination_id = u.id " .
        "WHERE m.id IN " .
        "(SELECT MAX(m2.id) as maxid FROM Message m2 WHERE m.user_origin_id = :user_id OR m.user_destination_id = :user_id GROUP BY LEAST(m2.user_origin_id,m2.user_destination_id), GREATEST(m2.user_origin_id,m2.user_destination_id)) " .
        "ORDER BY m.date DESC ";
       
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('ColectaUserBundle:Message', 'm');
        $rsm->addJoinedEntityFromClassMetadata('ColectaUserBundle:User', 'u', 'm', 'destination', array('id' => 'destination_id'));
        
        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('user_id', $user->getId());
        
        $messages = $query->getResult();
        
        return $this->render('ColectaUserBundle:Message:index.html.twig', array('messages' => $messages));
    }
    
    public function conversationAction($user_id)
    {
        $user = $this->getUser();
        
        if(!$user)
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $interlocutor = $em->getRepository('ColectaUserBundle:User')->findOneById($user_id);
        
        $messages = $em->createQuery(
            "SELECT m FROM ColectaUserBundle:Message m WHERE m.origin = :user_id and m.destination = :interlocutor_id OR m.destination = :user_id and m.origin = :interlocutor_id ORDER BY m.date DESC"
        )->setParameter('user_id', $user->getId())->setParameter('interlocutor_id', $interlocutor->getId())->getResult();
        
        if(count($messages))
        {
            for($i = 0; $i < count($messages); $i++)
            {
                if(!$messages[$i]->getDismiss())
                {
                    $messages[$i]->setDismiss(true);
                    $em->persist($messages[$i]);
                    
                    $em->flush();
                    
                    $this->get('session')->getFlashBag()->add('message'.$messages[$i]->getId() , 'unread');
                }
            }
        }
        
        return $this->render('ColectaUserBundle:Message:conversation.html.twig', array('messages' => $messages, 'interlocutor' => $interlocutor));
    }
    
    public function sentAction()
    {
        $user = $this->getUser();
        
        if(!$user)
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $messages = $em->getRepository('ColectaUserBundle:Message')->findBy(array('origin'=>$user->getId()), array('date'=>'DESC'),30,0);
        
        return $this->render('ColectaUserBundle:Message:sent.html.twig', array('messages' => $messages));
    }
    
    public function newAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $request = $this->get('request');
        
        if(!$user || !$user->getRole()->getMessageSend()) 
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        if ($request->getMethod() == 'POST') 
        {
            $persist = true;
            
            $destination = $request->request->get('destination');
            $text = $request->request->get('text');
            
            if(empty($destination))
            {
                $this->get('session')->getFlashBag()->add('MessageDestinationError',true);
                $this->get('session')->getFlashBag()->add('error', 'Debes indicar un destinatario');
                $persist = false;
            }
            else
            {
                $destinationUser = $em->getRepository('ColectaUserBundle:User')->findOneByName($destination);
                
                if(!$destinationUser)
                {
                    $this->get('session')->getFlashBag()->add('MessageDestinationError',true);
                    $this->get('session')->getFlashBag()->add('error', 'No existe un usuario con ese nombre');
                    $persist = false;
                }
            }
            
            if(empty($text))
            {
                $this->get('session')->getFlashBag()->add('MessageTextError',true);
                $this->get('session')->getFlashBag()->add('error', 'No puedes dejar el texto vacío');
                $persist = false;
            }
            
            if($persist)
            {
                try
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
                    
                    $this->get('session')->getFlashBag()->add('success', 'Mensaje enviado.');
                }
                catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add('error', 'No se ha podido notificar por email al receptor, pero el mensaje ha sido correctamente almacenado.');
                }
                
                return new RedirectResponse($this->generateUrl('userConversation', array('user_id' => $destinationUser->getId())));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('MessageDestination',$destination);
                $this->get('session')->getFlashBag()->add('MessageText',$text);
                
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
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $userTo = $em->getRepository('ColectaUserBundle:User')->find($user_id);
        
        if(!$user || !$user->getRole()->getMessageSend()) 
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
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
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $request = $this->get('request');
        
        if(!$user || !$user->getRole()->getMessageSend()) 
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $responsetoMessage = $em->getRepository('ColectaUserBundle:Message')->findOneById($responseto);
        
        if(!$responsetoMessage)
        {
            $this->get('session')->getFlashBag()->add('error', 'No se ha encontrado la conversación');
            return new RedirectResponse($this->generateUrl('userMessages'));
        }
        
        if ($request->getMethod() == 'POST') 
        {
            $persist = true;
            
            $text = $request->request->get('text');
            $destinationUser = $responsetoMessage->getOrigin();
            
            if(!$destinationUser)
            {
                $this->get('session')->getFlashBag()->add('MessageDestinationError',true);
                $this->get('session')->getFlashBag()->add('error', 'No existe el usuario en la base de datos');
                $persist = false;
            }
            
            if(empty($text))
            {
                $this->get('session')->getFlashBag()->add('MessageTextError',true);
                $this->get('session')->getFlashBag()->add('error', 'No puedes dejar el texto vacío');
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
                
                $this->get('session')->getFlashBag()->add('success', 'Mensaje enviado.');
                
                return new RedirectResponse($this->generateUrl('userMessages'));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('MessageDestination',$destinationUser->getName());   
                $this->get('session')->getFlashBag()->add('MessageText',$text);
                
                return $this->render('ColectaUserBundle:Message:response.html.twig', array('originalMessage'=>$responsetoMessage, 'destination'=>$responsetoMessage->getOrigin()->getName()));
            }
        }
        else
        {
            return $this->render('ColectaUserBundle:Message:response.html.twig', array('originalMessage'=>$responsetoMessage, 'destination'=>$responsetoMessage->getOrigin()->getName()));
        }
    }
}
