<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Colecta\ActivityBundle\Form\Frontend\RouteType;
use Colecta\ActivityBundle\Entity\Route;
use Colecta\ActivityBundle\Entity\RouteTrackpoint;
use Colecta\ActivityBundle\Entity\Place;
use Colecta\ItemBundle\Entity\Relation;
use Colecta\ItemBundle\Entity\Category;
use Colecta\UserBundle\Entity\Notification;


class DefaultController extends Controller
{
    private $ipp = 10; //Items per page
    
    public function indexAction()
    {
        return $this->pageAction(1);
    }
    
    public function pageAction($page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getManager();
        
        $SQLprivacy = '';
        
        if(!$this->getUser())
        {
            $SQLprivacy = ' AND i.open = 1 ';
        }
        
        //Get ALL the routes and places that are not drafts
        $items = $em->createQuery("SELECT i FROM ColectaItemBundle:Item i WHERE (i INSTANCE OF Colecta\ActivityBundle\Entity\Route OR i INSTANCE OF Colecta\ActivityBundle\Entity\Place) AND i.draft = 0 $SQLprivacy ORDER BY i.date DESC")->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1)->getResult();
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE (c.posts + c.routes + c.events + c.files + c.places) > 0 ORDER BY c.posts + c.routes + c.events + c.files + c.places DESC'
        )->setFirstResult(0)->setMaxResults(50);
        
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
        
        return $this->render('ColectaActivityBundle:Default:index.html.twig', array('items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
}