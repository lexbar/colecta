<?php

namespace Colecta\FilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class FolderController extends Controller
{
    
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug);
        
        return $this->render('ColectaFilesBundle:Folder:full.html.twig', array('item' => $item));
    }
}
