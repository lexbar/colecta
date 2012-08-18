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
    
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $items = $em->getRepository('ColectaItemBundle:Item')->findByDraft(0);
        
        /*$items = array();
        
        $item = new Folder();
        $item->setName('Carpeta de prueba'); 
        $item->setSlug('carpeta_de_prueba');
        $item->setSummary('Carpeta de prueba que contendra un archivo llamado Wanderful Life');
        $item->setTagwords('hola adios que tal');
        $item->setDate(new \DateTime('now'));
        $item->setAllowComments(true);
        $item->setDraft(false);
        //
        $item->setPublic(true);
        $item->setPersonal(true);
        $em->persist($item); 
        
        $items[] = $item;
        
        $folder = $item;
        
        $item = new File();
        $item->setName('Wonderful Life!'); 
        $item->setSlug('wonderful_life');
        $item->setSummary('Esto es una prueba para el resumen');
        $item->setTagwords('hola adios que tal');
        $item->setDate(new \DateTime('now'));
        $item->setAllowComments(true);
        $item->setDraft(false);
        //
        $item->setUrl('http://24.media.tumblr.com/tumblr_m8o9hlNmPh1rb86ldo1_400.jpg');
        $item->setFiletype('jpg');
        $item->setDescription('Esta es la descripcion');
        $item->setFolder($folder);
        $em->persist($item); 
        
        $items[] = $item;
        
        $item = new Place();
        $item->setName('Valencia'); 
        $item->setSlug('valencia');
        $item->setSummary('La ciudad de Valencia');
        $item->setTagwords('valencia ciudad lugar valenciano');
        $item->setDate(new \DateTime('now'));
        $item->setAllowComments(true);
        $item->setDraft(false);
        //
        $item->setLatitude(39.28);
        $item->setLongitude(0.22);
        $item->setDescription('La ciudad de Valencia');
        $em->persist($item); 
        
        $items[] = $item;
        
        $activity = new Activity();
        $activity->setName('Pachanga');
        $em->persist($activity); 
        
        $item = new Event();
        $item->setName('Fiesta de fin de año'); 
        $item->setSlug('fiesta_de_fin_de_ano');
        $item->setSummary('Esta es una fiesta muy exclusiva');
        $item->setTagwords('fiesta fin de año ano celebracion');
        $item->setDate(new \DateTime('now'));
        $item->setAllowComments(true);
        $item->setDraft(false);
        //
        $item->setDateini(new \DateTime('now + 2 days'));
        $item->setDateend(new \DateTime('now + 3 days'));
        $item->setShowhours(false);
        $item->setDescription('Esta es una fiesta muy exclusiva');
        $item->setDistance(39.28);
        $item->setUphill(204);
        $item->setDownhill(152);
        $item->setDifficulty('low');
        $item->setStatus('Regular');
        $item->setActivity($activity);
        $em->persist($item); 
        
        $items[] = $item;
        
        $em->flush();*/
        
        return $this->render('ColectaItemBundle:Default:index.html.twig', array('items' => $items));
    }
    
}
