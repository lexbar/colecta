<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Colecta\ItemBundle\Entity\Post;

class PostController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $items = $em->getRepository('ColectaItemBundle:Post')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaItemBundle:Post:index.html.twig', array('items' => $items));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaItemBundle:Post')->findOneBySlug($slug);
        
        return $this->render('ColectaItemBundle:Post:full.html.twig', array('item' => $item));
    }
}
