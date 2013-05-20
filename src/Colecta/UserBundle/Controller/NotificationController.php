<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\UserBundle\Entity\Notification;

class NotificationController extends Controller
{
    
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            return new RedirectResponse($this->generateUrl('UserLogin'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        // TEMPORARY
            $conn = $em->getConnection();
            $stmt = $conn->query("SELECT * FROM NotificationB");
            
            while ($row = $stmt->fetch()) {
                $notification = $em->getRepository('ColectaUserBundle:Notification')->findOneById($row['id']);
                
                if(preg_match('/(.*) va a asistir a :item:([0-9]+):/', $row['text'], $matches))
                {
                    $notification->setWhat('assist');
                    
                    $who = $em->getRepository('ColectaUserBundle:User')->findOneByName($matches[1]);
                    $notification->setWho($who);
                    
                    $item = $em->getRepository('ColectaItemBundle:Item')->findOneById($matches[2]);
                    $notification->setItem($item);
                }
                elseif(preg_match('/(.*) ya no asiste a :item:([0-9]+):/', $row['text'], $matches))
                {
                    $notification->setWhat('unassist');
                    
                    $who = $em->getRepository('ColectaUserBundle:User')->findOneByName($matches[1]);
                    $notification->setWho($who);
                    
                    $item = $em->getRepository('ColectaItemBundle:Item')->findOneById($matches[2]);
                    $notification->setItem($item);
                }
                elseif(preg_match('/(.*) ha escrito un comentario en :item:([0-9]+):/', $row['text'], $matches))
                {
                    $notification->setWhat('comment');
                    
                    $who = $em->getRepository('ColectaUserBundle:User')->findOneByName($matches[1]);
                    $notification->setWho($who);
                    
                    $item = $em->getRepository('ColectaItemBundle:Item')->findOneById($matches[2]);
                    $notification->setItem($item);
                }
                elseif(preg_match('/(.*) ha contestado en :item:([0-9]+):/', $row['text'], $matches))
                {
                    $notification->setWhat('reply');
                    
                    $who = $em->getRepository('ColectaUserBundle:User')->findOneByName($matches[1]);
                    $notification->setWho($who);
                    
                    $item = $em->getRepository('ColectaItemBundle:Item')->findOneById($matches[2]);
                    $notification->setItem($item);
                }
                elseif(preg_match('/(.*) ha comentado en :item:([0-9]+):/', $row['text'], $matches))
                {
                    $notification->setWhat('comment');
                    
                    $who = $em->getRepository('ColectaUserBundle:User')->findOneByName($matches[1]);
                    $notification->setWho($who);
                    
                    $item = $em->getRepository('ColectaItemBundle:Item')->findOneById($matches[2]);
                    $notification->setItem($item);
                }
                
                $em->persist($notification); 
                $em->flush();
            }
        // TEMPORARY
        
        $notifications = $em->getRepository('ColectaUserBundle:Notification')->findBy(array('user'=>$user->getId()), array('date'=>'DESC'),30,0);
        
        return $this->render('ColectaUserBundle:Notification:index.html.twig', array('notifications' => $notifications));
    }
    public function dismissAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        $notification = $em->getRepository('ColectaUserBundle:Notification')->findOneBy(array('user'=>$user->getId(), 'id'=>$id));
        
        if(!$user) 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif(!$notification)
        {
            $this->get('session')->setFlash('error', 'No existe la notificación');
        }
        else 
        {
            $notification->setDismiss(true);
            
            $em->persist($notification); 
            $em->flush();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('UserNotifications');
        }
        
        return new RedirectResponse($referer);
    }
}
