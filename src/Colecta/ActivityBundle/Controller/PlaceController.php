<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ActivityBundle\Entity\Place;

class PlaceController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaActivityBundle:Place')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();

        return $this->render('ColectaActivityBundle:Place:index.html.twig', array('items' => $items, 'categories' => $categories));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Place')->findOneBySlug($slug);
        
        return $this->render('ColectaActivityBundle:Place:full.html.twig', array('item' => $item));
    }
    public function createAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
    
        if(!$user) 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif(!$request->get('name'))
        {
            $this->get('session')->setFlash('error', 'No has escrito ningun nombre');
        }
        elseif(!$category)
        {
            $this->get('session')->setFlash('error', 'No existe la categoria');
        }
        else
        {
            $place= new Place();
            $place->setCategory($category);
            $place->setAuthor($user);
            $place->setName($request->get('name'));
            
            //Slug generate
            $slug = $place->generateSlug();
            $n = 2;
            
            while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
            {
                if($n > 2)
                {
                    $slug = substr($slug,0,-2);
                }
                
                $slug .= '_'.$n;
                
                $n++;
            }
            $place->setSlug($slug);
            
            $place->summarize($request->get('description'));
            $place->setAllowComments(true);
            $place->setDraft(false);
            $place->setDescription($request->get('description'));
            $place->setLatitude($request->get('latitude'));
            $place->setLongitude($request->get('longitude'));
            
            $em->persist($place); 
            $em->flush();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaPlaceIndex');
        }
        
        return new RedirectResponse($referer);
    }

}
