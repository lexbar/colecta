<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Colecta\ItemBundle\Entity\Post;
use Colecta\FilesBundle\Entity\File;
use Colecta\FilesBundle\Entity\Folder;
use Colecta\ActivityBundle\Entity\Activity;
use Colecta\ActivityBundle\Entity\Place;
use Colecta\ActivityBundle\Entity\Event;
use Colecta\ActivityBundle\Entity\Route;

class DefaultController extends Controller
{
    
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaItemBundle:Default:index.html.twig', array('items' => $items));
    }
    
    public function searchresultsAction($search)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        //TODO: FULLTEXT SQL
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaItemBundle:Default:index.html.twig', array('items' => $items));
    }
    
}
