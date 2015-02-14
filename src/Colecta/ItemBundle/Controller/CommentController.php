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
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $post = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug);
        
        if(!$item)
        {
            $this->get('session')->getFlashBag()->add('error', 'No hemos encontrado el contenido que quieres comentar');
        }
        elseif(!$user || !$item->canComment($user))
        {
            $this->get('session')->getFlashBag()->add('error', 'No tienes permisos para publicar un comenario');
        }
        elseif(!$post->get('comment')) 
        {
            $this->get('session')->getFlashBag()->add('error', 'No puedes publicar un comenario vacío');
        }
        else
        {
            $comment = new Comment();
            $comment->setItem($item);
            $comment->setUser($user);
            $comment->setText($post->get('comment'));
            $comment->setDate(new \DateTime('now'));
            $item->setLastInteraction(new \DateTime('now'));
            
            $em->persist($comment); 
            
            $item->getCategory()->setLastchange(new \DateTime('now'));
            $em->persist($item); 
            
            $notifications = array($user->getId());
            
            if($user != $item->getAuthor())
            {
                //Notification to the owner
                $notification = $em->getRepository('ColectaUserBundle:Notification')->findOneBy(array('user'=>$item->getAuthor(),'dismiss'=>0,'what'=>'comment','item'=>$item));
                    
                if($notification)
                {
                    if($notification->getWho() != $user) 
                    {
                        $notification->setPluspeople(intval($notification->getPluspeople()) + 1);
                    }
                    $notification->setDate(new \DateTime('now'));
                }
                else
                {
                    $notification = new Notification();
                    $notification->setUser($item->getAuthor());
                    $notification->setDismiss(0);
                    $notification->setDate(new \DateTime('now'));
                    $notification->setWhat('comment');
                    $notification->setWho($user);
                    $notification->setItem($item);
                    //$notification->setText($user->getName().' ha escrito un comentario en :item:'.$item->getId().':');
                }
                $em->persist($notification); 
                
                $notifications[] = $item->getAuthor()->getId();
            }
            
            $comments = $item->getComments();
            
            foreach($comments as $c) 
            {
                if(!in_array($c->getUser()->getId(), $notifications)) //if he has not already received a notification..
                {
                    //Notification to a subscriber
                    $notification = $em->getRepository('ColectaUserBundle:Notification')->findOneBy(array('user'=>$c->getUser(),'dismiss'=>0,'what'=>'reply','item'=>$item));
                    
                    if($notification)
                    {
                        if($notification->getWho() != $user) 
                        {
                            $notification->setPluspeople(intval($notification->getPluspeople()) + 1);
                        }
                        $notification->setDate(new \DateTime('now'));
                    }
                    else
                    {
                        $notification = new Notification();
                        $notification->setUser($c->getUser());
                        $notification->setDismiss(0);
                        $notification->setDate(new \DateTime('now'));
                        $notification->setWhat('reply');
                        $notification->setWho($user);
                        $notification->setItem($item);
                        //$notification->setText($user->getName().' ha contestado en :item:'.$item->getId().':');
                    }
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
                        $notification = $em->getRepository('ColectaUserBundle:Notification')->findOneBy(array('user'=>$a->getUser(),'dismiss'=>0,'what'=>'comment','item'=>$item));
                    
                        if($notification)
                        {
                            if($notification->getWho() != $user) 
                            {
                                $notification->setPluspeople(intval($notification->getPluspeople()) + 1);
                            }
                            $notification->setDate(new \DateTime('now'));
                        }
                        else
                        {
                            $notification = new Notification();
                            $notification->setUser($a->getUser());
                            $notification->setDismiss(0);
                            $notification->setDate(new \DateTime('now'));
                            $notification->setWhat('comment');
                            $notification->setWho($user);
                            $notification->setItem($item);
                            //$notification->setText($user->getName().' ha comentado en :item:'.$item->getId().':');
                        }
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
    
    function removeAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.') 
        {
            $this->get('session')->getFlashBag()->add('error', 'Debes iniciar sesión');
        }
        else
        {
            $comment = $em->getRepository('ColectaItemBundle:Comment')->findOneById($id);
            
            if(!$comment)
            {
                $this->get('session')->getFlashBag()->add('error', 'El comentario no existe');
            }
            elseif($comment->getUser() != $user && !$user->getRole()->getUserEdit()) //if user can edit any other user, means he can change any of their data (including comments)
            {
                $this->get('session')->getFlashBag()->add('error', 'No tienes permiso para borrar este comentario');
            }
            else
            {
                $em->remove($comment);
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('sucess', 'Mensaje eliminado');
            }
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaDashboard');
        }
        
        return new RedirectResponse($referer);
    }
}
