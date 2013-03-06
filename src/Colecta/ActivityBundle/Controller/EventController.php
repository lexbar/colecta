<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ActivityBundle\Entity\Event;
use Colecta\ActivityBundle\Entity\EventAssistance;
use Colecta\ItemBundle\Entity\Category;
use Colecta\UserBundle\Entity\Notification;


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
    public function newAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaActivityBundle:Event:new.html.twig', array('categories' => $categories));
    }
    public function dateAction($date)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        try 
        {
            $myDate = new \DateTime($date);
        }
        catch(Exception $e)
        {
            $myDate = false;
        }
        
        if($myDate)
        {
            $query = $em->createQuery("SELECT e FROM ColectaActivityBundle:Event e WHERE e.dateini >= '".$myDate->format('Y:m:d 00:00:00')."' AND e.dateini <= '".$myDate->format('Y:m:d 23:59:59')."' ORDER BY e.dateini ASC");
    
            $items = $query->getResult();      
        }
        else
        {
            $items = array();
        }
        
        return $this->render('ColectaActivityBundle:Event:date.html.twig', array('items' => $items, 'date' => $myDate));
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
            $item = new Event();
            
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
            $item->setActivity(null);
            $item->setDateini(new \DateTime(trim($request->get('dateini')).' '.$request->get('dateinihour').':'.$request->get('dateiniminute')));
            $item->setDateend(new \DateTime(trim($request->get('dateend')).' '.$request->get('dateendhour').':'.$request->get('dateendminute')));
            $item->setShowhours(false);
            $item->setDescription($request->get('description'));
            $item->setDistance(str_replace(',','.', $request->get('distance')));
            $item->setUphill($request->get('uphill'));
            $item->setDownhill(0);
            $item->setDifficulty($request->get('difficulty'));
            $item->setStatus('');
            
            $em->persist($item); 
            $em->flush();
        }
        
        if(isset($item))
        {
            return new RedirectResponse($this->generateUrl('ColectaEventView',array('slug'=>$item->getSlug())));
        }
        else
        {
            $this->get('session')->setFlash('EventName', $request->get('name'));
            $this->get('session')->setFlash('EventDescription', $request->get('description'));
            $this->get('session')->setFlash('EventDateini', $request->get('dateini'));
            $this->get('session')->setFlash('EventDateend', $request->get('dateend'));
            return new RedirectResponse($this->generateUrl('ColectaEventNew'));
        }
    }
    
    public function editAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if(!$user || $user != $item->getAuthor()) 
        {
            return new RedirectResponse($this->generateUrl('ColectaPostView', array('slug'=>$slug)));
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
            
            $item->summarize($request->get('description'));
            $item->setAllowComments(true);
            $item->setDraft(false);
            $item->setActivity(null);
            $item->setDateini(new \DateTime(trim($request->get('dateini')).' '.$request->get('dateinihour').':'.$request->get('dateiniminute')));
            $item->setDateend(new \DateTime(trim($request->get('dateend')).' '.$request->get('dateendhour').':'.$request->get('dateendminute')));
            $item->setShowhours(false);
            $item->setDescription($request->get('description'));
            $item->setDistance(str_replace(',','.', $request->get('distance')));
            $item->setUphill($request->get('uphill'));
            $item->setDownhill(0);
            $item->setDifficulty($request->get('difficulty'));
            $item->setStatus('');
            
            if($persist)
            {
                $em->persist($item); 
                $em->flush();
                $this->get('session')->setFlash('success', 'Modificado con éxito.');
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        return $this->render('ColectaActivityBundle:Event:edit.html.twig', array('item' => $item, 'categories'=>$categories));
    }
    
    
    public function assistanceAction($slug)
    /*
        Mark Assistance of logged User to an Event
        
        From the Event node
    */
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        elseif(!$item)
        {
            $this->get('session')->setFlash('error', 'No existe el evento');
        }
        else
        {
            $assistance = $em->getRepository('ColectaActivityBundle:EventAssistance')->findOneBy(array('event'=>$item->getId(),'user'=>$user->getId()));
            
            if($assistance)
            {
                $this->get('session')->setFlash('error', 'Ya has marcado tu asistencia');
            }
            else
            {
                $assistance = new EventAssistance();
                $assistance->setConfirmed(0);
                $assistance->setKm($item->getDistance());
                $assistance->setUser($user);
                $assistance->setEvent($item);
                
                /* Notification to owner */
                if(1 || $user != $item->getAuthor())
                {
                    $notification = new Notification();
                    // text date dismiss user
                    $notification->setUser($item->getAuthor());
                    $notification->setDismiss(0);
                    $notification->setDate(new \DateTime('now'));
                    $notification->setText($user->getName().' va a asistir a '.$item->getName());
                    
                    $em->persist($notification); 
                }
                
                $em->persist($assistance); 
                $em->flush();
            }
        }
        
        if($item)
        {
            $referer = $this->generateUrl('ColectaEventView', array('slug'=>$item->getSlug()));
        }
        else
        {
            $referer = $this->get('request')->headers->get('referer');
            
            if(empty($referer))
            {
                $referer = $this->generateUrl('ColectaEventView');
            }
        }
        
        
        return new RedirectResponse($referer);
    }
    
    
    /*
        Update Assistances
        
        From the menu-list of assistances.
        
        First checks for changes on the list.
        Finally checks for user asssistance by username
    */
    public function updateAssistancesAction($slug)
    {
        $request = $this->get('request')->request;
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        elseif(!$item)
        {
            $this->get('session')->setFlash('error', 'No existe el evento');
        }
        elseif($item->getAuthor() != $user)
        {
            $this->get('session')->setFlash('error', 'No tienes permiso para gestionar las asistencias de este evento');
        }
        else
        {
            if($item->hasAssistances())
            {
                $assistances = $item->getAssistances();
                foreach($assistances as $ass)
                {
                    $id = $ass->getUser()->getId().'';
                    if($request->get('user'.$id.'confirmed'))
                    {
                        $ass->setConfirmed(true);
                    }
                    else
                    {
                        $ass->setConfirmed(false);
                    }
                    
                    $ass->setKm(str_replace(',','.', $request->get('user'.$id.'km')));
                    
                    $em->persist($ass); 
                    $em->flush();
                }
            }
            if($request->get('targetUser'))
            {
                //Check if Target User exists
                $targetUser = $em->getRepository('ColectaUserBundle:User')->findOneByName($request->get('targetUser'));
                if(! $targetUser)
                {
                    $this->get('session')->setFlash('error', 'No hemos encontrado al usuario que indicas');
                }
                else
                {
                    //Check if assistance already exists for this user
                    $assistance = $em->getRepository('ColectaActivityBundle:EventAssistance')->findOneBy(array('event'=>$item->getId(),'user'=>$targetUser->getId()));
                    
                    if($assistance)
                    {
                        $this->get('session')->setFlash('error', 'El usuario ya ha marcado su asistencia');
                    }
                    else
                    {
                        $assistance = new EventAssistance();
                        $assistance->setConfirmed(1);
                        $assistance->setKm($item->getDistance());
                        $assistance->setUser($targetUser);
                        $assistance->setEvent($item);
                        
                        $em->persist($assistance); 
                        $em->flush();
                    }                
                }
            }
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaEventIndex');
        }
        
        return new RedirectResponse($referer);
    }
    
    public function calendarAction($date)
    {
        $dateOb = new \DateTime($date);
        $em = $this->getDoctrine()->getEntityManager();
        $items = $em->createQuery('SELECT e FROM ColectaActivityBundle:Event e WHERE e.draft = 0 AND e.dateini >= \''.$dateOb->format('Y-m-1 00:00:00').'\' AND e.dateini < \''.$dateOb->format('Y-').(intval($dateOb->format('m'))+1).'-1 00:00:00'.'\' ORDER BY e.date ASC')->getResult();
        
        $ev = array();
        for($i = 1; $i <= 31; $i++){$ev[$i] = array();}
        
        if(count($items))
        {
            foreach($items as $item)
            {
                $day = intval($item->getDateini()->format('j'));
                $ev[$day][] = $item;
            }
        }
        
        return $this->render('ColectaActivityBundle:Event:calendar.html.twig', array('events' => $ev, 'targetdate'=>$date, 'targetdateob'=>$dateOb));
    }
}
