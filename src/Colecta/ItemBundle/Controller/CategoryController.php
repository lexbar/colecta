<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategoryController extends Controller
{
    private $ipp = 10; //Items per page
    
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findBy(array(),array('lastchange'=>'DESC'));
        
        return $this->render('ColectaItemBundle:Category:index.html.twig', array('categories'=>$categories));
    }
    public function viewAction($slug)
    {
        return $this->pageAction($slug, 1);
    }    
    public function pageAction($slug, $page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneBySlug($slug);
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft' => 0,'part'=>0, 'category' => $category->getId()), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
        //Pagination
        if(count($items) > $this->ipp) 
        {
            $thereAreMore = true;
            unset($items[$this->ipp]);
        }
        else
        {
            $thereAreMore = false;
        }
        
        return $this->render('ColectaItemBundle:Category:page.html.twig', array('category'=>$category, 'items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    
    public function formlistAction($selected)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaItemBundle:Category:categoryform.html.twig', array('categories'=>$categories, 'selected'=>$selected));
    }
}
