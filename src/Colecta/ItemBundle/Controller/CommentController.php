<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Colecta\ItemBundle\Entity\Comment;

class CommentController extends Controller
{
    
    public function commentAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $post = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug);
    
        if(!$item || !$user || !$post->get('comment')) 
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
