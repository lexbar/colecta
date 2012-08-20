<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategoryController extends Controller
{
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneBySlug($slug);
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft' => 0, 'category' => $category->getId()), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaItemBundle:Category:index.html.twig', array('items' => $items));
    }
}
