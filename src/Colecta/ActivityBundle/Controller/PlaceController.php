<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ActivityBundle\Entity\Place;
use Colecta\ItemBundle\Entity\Category;
use Colecta\UserBundle\Entity\Notification;

class PlaceController extends Controller
{
    private $ipp = 10; //Items per page
    
    public function indexAction()
    {
        return $this->pageAction(1);
    }
    
    public function pageAction($page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaActivityBundle:Place')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
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
        
        return $this->render('ColectaActivityBundle:Place:index.html.twig', array('items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Place')->findOneBySlug($slug);
        
        return $this->render('ColectaActivityBundle:Place:full.html.twig', array('item' => $item));
    }
    public function newAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaActivityBundle:Place:new.html.twig', array('categories' => $categories));
    }
    public function createAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
    
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        elseif(!$request->get('description'))
        {
            $this->get('session')->setFlash('error', 'No has escrito ningun texto');
        }
        elseif(!$category)
        {
            $this->get('session')->setFlash('error', 'No existe la categoria');
        }
        else
        {
            $item = new Place();
            
            if($request->get('newCategory'))
            {
                $category = new Category();
                $category->setName($request->get('newCategory'));
                
                //Category Slug generate
                $catSlug = $item->generateSlug($request->get('newCategory'));
                $n = 2;
                
                while($em->getRepository('ColectaItemBundle:Category')->findOneBySlug($catSlug)) 
                {
                    if($n > 2)
                    {
                        $catSlug = substr($catSlug,0,-2);
                    }
                    
                    $catSlug .= '_'.$n;
                    
                    $n++;
                }
            
                $category->setSlug($catSlug);
                $category->setDescription('');
                $category->setLastchange(new \DateTime('now'));
                
                $em->persist($category); 
            }
            
            $item->setCategory($category);
            $item->setAuthor($user);
            $item->setName($request->get('name'));
            
            //Slug generate
            if(strlen($request->get('name')) == 0)
            {
                $slug = 'untitled';
            }
            else
            {
                $slug = $item->generateSlug();
            }
            
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
            $item->setSlug($slug);
            
            $item->summarize($request->get('description'));
            $item->setAllowComments(true);
            $item->setDraft(false);
            $item->setPart(false);
            $item->setDescription($request->get('description'));
            $item->setLatitude($request->get('latitude'));
            $item->setLongitude($request->get('longitude'));
            
            $em->persist($item); 
            $em->flush();
        }
        
        if(isset($item))
        {
            return new RedirectResponse($this->generateUrl('ColectaPlaceView',array('slug'=>$item->getSlug())));
        }
        else
        {
            $this->get('session')->setFlash('PlaceName', $request->get('name'));
            $this->get('session')->setFlash('PlaceDescription', $request->get('description'));
            $this->get('session')->setFlash('PlaceLatitude', $request->get('latitude'));
            $this->get('session')->setFlash('PlaceLongitude', $request->get('longitude'));
            return new RedirectResponse($this->generateUrl('ColectaPlaceNew'));
        }
    }
    public function editAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaActivityBundle:Place')->findOneBySlug($slug);
        
        if(!$user || $user != $item->getAuthor()) 
        {
            return new RedirectResponse($this->generateUrl('ColectaPlaceView', array('slug'=>$slug)));
        }
        
        if ($this->get('request')->getMethod() == 'POST') {
            $persist = true;
            
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
        
            if(!$category)
            {
                $this->get('session')->setFlash('error', 'No existe la categoria');
                $persist = false;
            }
            else
            {
                if($request->get('newCategory'))
                {
                    $category = new Category();
                    $category->setName($request->get('newCategory'));
                    
                    //Category Slug generate
                    $catSlug = $item->generateSlug($request->get('newCategory'));
                    $n = 2;
                    
                    while($em->getRepository('ColectaItemBundle:Category')->findOneBySlug($catSlug)) 
                    {
                        if($n > 2)
                        {
                            $catSlug = substr($catSlug,0,-2);
                        }
                        
                        $catSlug .= '_'.$n;
                        
                        $n++;
                    }
                
                    $category->setSlug($catSlug);
                    $category->setDescription('');
                    $category->setLastchange(new \DateTime('now'));
                    
                    $em->persist($category); 
                }
                
                $item->setCategory($category);
            }
            
            if(!$request->get('description'))
            {
                $this->get('session')->setFlash('error', 'No puedes dejar vacío el texto');
                $persist = false;
            }
            
            $item->setName($request->get('name'));
            $item->summarize($request->get('description'));
            $item->setDescription($request->get('description'));
            $item->setLatitude($request->get('latitude'));
            $item->setLongitude($request->get('longitude'));
            
            if($persist)
            {
                $em->persist($item); 
                $em->flush();
                $this->get('session')->setFlash('success', 'Modificado con éxito.');
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        return $this->render('ColectaActivityBundle:Place:edit.html.twig', array('item' => $item, 'categories'=>$categories));
    }

}
