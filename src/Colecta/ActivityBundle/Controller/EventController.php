<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ActivityBundle\Entity\Event;


class EventController extends Controller
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
        $items = $em->getRepository('ColectaActivityBundle:Event')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
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
        
        return $this->render('ColectaActivityBundle:Event:index.html.twig', array('items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        return $this->render('ColectaActivityBundle:Event:full.html.twig', array('item' => $item));
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
            $event = new Event();
            $event->setCategory($category);
            $event->setAuthor($user);
            $event->setName($request->get('name'));
            
            //Slug generate
            $slug = $event->generateSlug();
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
            $event->setSlug($slug);
            
            $event->summarize($request->get('description'));
            $event->setAllowComments(true);
            $event->setDraft(false);
            $event->setActivity(null);
            $event->setDateini(new \DateTime(trim($request->get('dateini')).' '.$request->get('dateinihour').':'.$request->get('dateiniminute')));
            $event->setDateend(new \DateTime(trim($request->get('dateend')).' '.$request->get('dateendhour').':'.$request->get('dateendminute')));
            $event->setShowhours(false);
            $event->setDescription($request->get('description'));
            $event->setDistance($request->get('distance'));
            $event->setUphill($request->get('uphill'));
            $event->setDownhill($request->get('downhill'));
            $event->setDifficulty($request->get('difficulty'));
            $event->setStatus('');
            
            $em->persist($event); 
            $em->flush();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaEventIndex');
        }
        
        return new RedirectResponse($referer);
    }
}
