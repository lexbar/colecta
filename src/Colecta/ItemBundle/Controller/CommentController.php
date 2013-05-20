<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\UserBundle\Entity\Notification;
use Colecta\ItemBundle\Entity\Comment;

class CommentController extends Controller
{
    
    public function commentAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $post = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug);
    
        if(!$item || $user == 'anon.' || !$post->get('comment')) 
        {
            $this->get('session')->setFlash('error', 'Error escribiendo el comentario');
        }
        else
        {
            $comment = new Comment();
            $comment->setItem($item);
            $comment->setUser($user);
            $comment->setText($post->get('comment'));
            $comment->setDate(new \DateTime('now'));
            
            $em->persist($comment); 
            
            $notifications = array($user->getId());
            
            if($user != $item->getAuthor())
            {
                //Notification to the owner
                $notification = new Notification();
                $notification->setUser($item->getAuthor());
                $notification->setDismiss(0);
                $notification->setDate(new \DateTime('now'));
                $notification->setWhat('comment');
                $notification->setWho($user);
                $notification->setItem($item);
                //$notification->setText($user->getName().' ha escrito un comentario en :item:'.$item->getId().':');
                
                $em->persist($notification); 
                
                $notifications[] = $item->getAuthor()->getId();
            }
            
            $comments = $item->getComments();
            
            foreach($comments as $c) 
            {
                if(!in_array($c->getUser()->getId(), $notifications)) //if he has not already received a notification..
                {
                    //Notification to a subscriber
                    $notification = new Notification();
                    $notification->setUser($c->getUser());
                    $notification->setDismiss(0);
                    $notification->setDate(new \DateTime('now'));
                    $notification->setWhat('reply');
                    $notification->setWho($user);
                    $notification->setItem($item);
                    //$notification->setText($user->getName().' ha contestado en :item:'.$item->getId().':');
                    
                    $em->persist($notification); 
                    
                    $notifications[] = $c->getUser()->getId();
                }
            }
                 
            if($item->getType() == 'Activity/Event')
            {
                $assistances = $item->getAssistances();
                foreach($assistances as $a) 
                {
                    if(!in_array($a->getUser()->getId(), $notifications)) //if he has not already received a notification..
                    {
                        //Notification to assistant
                        $notification = new Notification();
                        $notification->setUser($a->getUser());
                        $notification->setDismiss(0);
                        $notification->setDate(new \DateTime('now'));
                        $notification->setWhat('comment');
                        $notification->setWho($user);
                        $notification->setItem($item);
                        //$notification->setText($user->getName().' ha comentado en :item:'.$item->getId().':');
                        
                        $em->persist($notification); 
                        
                        $notifications[] = $a->getUser()->getId();
                    }
                }
            }  
                    
            $em->flush();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaDashboard');
        }
        
        return new RedirectResponse($referer);
    }
    
}
