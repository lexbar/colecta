<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
        
        $em = $this->getDoctrine()->getManager();
        
        $notifications = $em->getRepository('ColectaUserBundle:Notification')->findBy(array('user'=>$user->getId()), array('date'=>'DESC'),20,0);
        
        return $this->render('ColectaUserBundle:Notification:index.html.twig', array('notifications' => $notifications));
    }
    public function loadAction($page)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            return new RedirectResponse($this->generateUrl('UserLogin'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $notifications = $em->getRepository('ColectaUserBundle:Notification')->findBy(array('user'=>$user->getId()), array('date'=>'DESC'),20,20 * intval($page));
        
        $response = new Response($this->renderView('ColectaUserBundle:Notification:load.json.twig', array('notifications' => $notifications)),200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    public function dismissAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $notification = $em->getRepository('ColectaUserBundle:Notification')->findOneBy(array('user'=>$user->getId(), 'id'=>$id));
        
        if(!$user) 
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
        }
        elseif(!$notification)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe la notificación');
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
    public function dismissAllAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user) 
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
        }
        else 
        {
            $query = $em->createQuery('SELECT n FROM ColectaUserBundle:Notification n WHERE n.user = :uid AND n.dismiss = 0 ORDER BY n.date DESC');
            $query->setParameter('uid', $user->getId());
            
            $notifications = $query->getResult();
            
            foreach($notifications as $notification)
            {
                $notification->setDismiss(true);
                $em->persist($notification); 
            }
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
