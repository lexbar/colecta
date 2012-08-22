<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ActivityBundle\Entity\Event;


class EventController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $items = $em->getRepository('ColectaActivityBundle:Event')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaActivityBundle:Event:index.html.twig', array('items' => $items, 'categories' => $categories));
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
        elseif(!$request->get('text'))
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
                    $slug = substr($slug,-2,2);
                }
                
                $slug .= '_'.$n;
                
                $n++;
            }
            $event->setSlug($slug);
            
            $event->setSummary( substr($request->get('text'),0,255) );
            $event->setTagwords( substr($request->get('text'),255,255) );
            $event->setDate(new \DateTime('now'));
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
