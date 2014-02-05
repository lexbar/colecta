<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategoryController extends Controller
{
    private $ipp = 30; //Items per page
    
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c ORDER BY c.posts + c.routes + c.events + c.files + c.places DESC'
        )->setFirstResult(0)->setMaxResults(50);
        
        $categories = $query->getResult();
        
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
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft' => 0, 'category' => $category->getId()), array('lastInteraction'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
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
    
    public function formlistAction($selected, $simplified = false)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaItemBundle:Category:categoryform.html.twig', array('categories'=>$categories, 'selected'=>$selected, 'simplified'=>$simplified));
    }
}
