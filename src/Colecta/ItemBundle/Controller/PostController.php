<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Colecta\ItemBundle\Entity\Post;

class PostController extends Controller
{
    
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaItemBundle:Post')->findOneBySlug($slug);
        
        return $this->render('ColectaItemBundle:Post:full.html.twig', array('item' => $item));
    }
}
