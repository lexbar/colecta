<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategoryController extends Controller
{
    private $ipp = 30; //Items per page
    
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE (c.posts + c.routes + c.events + c.files + c.places) > 0 ORDER BY c.posts + c.routes + c.events + c.files + c.places DESC'
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
        
        $em = $this->getDoctrine()->getManager();
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneBySlug($slug);
        
        $SQLprivacy = '';
        
        if(!$this->getUser())
        {
            $SQLprivacy = ' AND i.open = 1 ';
        }
        
        $items = $em->createQuery(
            "SELECT i FROM ColectaItemBundle:Item i WHERE i.draft = 0 $SQLprivacy AND i.category = :category AND NOT i INSTANCE OF Colecta\FilesBundle\Entity\File ORDER BY i.date DESC"
        )->setParameter('category',$category->getId())->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1)->getResult();
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE c.posts + c.files + c.events + c.places + c.routes > 0 ORDER BY c.name ASC'
        )->setFirstResult(0);
        
        $categories = $query->getResult();
        
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
        
        return $this->render('ColectaItemBundle:Category:page.html.twig', array('category'=>$category, 'categories' => $categories, 'items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    
    public function formlistAction($selected, $simplified = false)
    {
        $em = $this->getDoctrine()->getManager();
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaItemBundle:Category:categoryform.html.twig', array('categories'=>$categories, 'selected'=>$selected, 'simplified'=>$simplified));
    }
}
