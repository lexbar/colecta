<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ActivityBundle\Entity\Event;
use Colecta\ActivityBundle\Entity\EventAssistance;
use Colecta\ItemBundle\Entity\Category;
use Colecta\ItemBundle\Entity\Relation;
use Colecta\UserBundle\Entity\Notification;
use Colecta\UserBundle\Entity\Points;


class EventController extends Controller
{
    private $ipp = 10; //Items per page
    
    public function indexAction()
    {
        $today = new \DateTime('today');
        return $this->dateAction($today->format('Y-m'));
    }
    
    public function pageAction($page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getManager();
        
        $findby = array('draft'=>0);
        
        if(!$this->getUser() || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $findby['open'] = 1;
        }
        
        //Get ALL the items that are accessible
        $items = $em->getRepository('ColectaActivityBundle:Event')->findBy($findby, array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE c.events > 0 ORDER BY c.name ASC'
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
        
        return $this->render('ColectaActivityBundle:Event:index.html.twig', array('items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    
    public function categoryAction($slug)
    {
        return $this->categoryPageAction($slug, 1);
    }    
    public function categoryPageAction($slug, $page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getManager();
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneBySlug($slug);
        
        $SQLprivacy = '';
        
        if(!$this->getUser() || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $SQLprivacy = ' AND i.open = 1 ';
        }
        
        $items = $em->createQuery(
            "SELECT i FROM ColectaItemBundle:Item i WHERE i.draft = 0 $SQLprivacy AND i.category = :category AND i INSTANCE OF Colecta\ActivityBundle\Entity\Event ORDER BY i.date DESC"
        )->setParameter('category',$category->getId())->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1)->getResult();
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE c.events > 0 ORDER BY c.name ASC'
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
        
        return $this->render('ColectaActivityBundle:Event:category.html.twig', array('category'=>$category, 'items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getUser();
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if(!$item)
        {
            $this->get('session')->getFlashBag()->add('error', 'No hemos encontrado la actividad que estás buscando');
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        if(($item->getDraft() && (! $user || $user->getId() != $item->getAuthor()->getId() )) || ((!$user || $user->getRole()->is('ROLE_BANNED')) && !$item->getOpen()))
        {
            $this->get('session')->getFlashBag()->add('error', 'No tienes permisos para ver esta actividad');
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        //Points
        $points = array();
        $pointsResult = $em->getRepository('ColectaUserBundle:Points')->findByItem($item);
        
        foreach($pointsResult as $point)
        {
            $points[$point->getUser()->getId()] = $point;
        }
        
        return $this->render('ColectaActivityBundle:Event:full.html.twig', array('item' => $item, 'points'=>$points));
    }
    public function newAction()
    {
        $user = $this->getUser();
        
        if(!$user || !$user->getRole()->getItemEventCreate()) 
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        return $this->render('ColectaItemBundle:Default:newItem.html.twig', array('type' => 'Event'));
    }
    public function dateAction($date)
    {
        $em = $this->getDoctrine()->getManager();
        
        try 
        {
            $myDate = new \DateTime($date);
        }
        catch(Exception $e)
        {
            $myDate = false;
        }
        
        $SQLprivacy = '';
        
        if(!$this->getUser() || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $SQLprivacy = ' AND e.open = 1 ';
        }
        
        if($myDate)
        {
            if(strlen($date) == 7) //format YYYY-MM
            {
                $ismonth = true;
                $items = $em->createQuery("SELECT e FROM ColectaActivityBundle:Event e WHERE e.draft = 0 $SQLprivacy AND e.dateini <= '".$myDate->format('Y-m-t 23:59:59')."' AND e.dateend >= '".$myDate->format('Y-m-1 00:00:00')."' ORDER BY e.dateini ASC")->getResult();
                
            }
            else
            {
                $ismonth = false;
                $items = $em->createQuery("SELECT e FROM ColectaActivityBundle:Event e WHERE e.draft = 0 $SQLprivacy AND e.dateini <= '".$myDate->format('Y-m-d 23:59:59')."' AND e.dateend >= '".$myDate->format('Y-m-d 00:00:00')."' ORDER BY e.dateini ASC")->getResult();
            } 
        }
        else
        {
            $items = array();
        }
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE c.events > 0 ORDER BY c.name ASC'
        )->setFirstResult(0)->setMaxResults(50);
        
        $categories = $query->getResult();
        
        return $this->render('ColectaActivityBundle:Event:date.html.twig', array('items' => $items, 'categories' => $categories, 'date' => $myDate, 'ismonth' => $ismonth));
    }
    public function detailsFormAction()
    {
        $user = $this->getUser();
        
        if(!$user)
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        else
        {
            return $this->render('ColectaActivityBundle:Event:detailsform.html.twig');
        }
    }
    public function createAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $request = $this->get('request')->request;
        
        if(!$user || !$user->getRole()->getItemEventCreate()) 
        {
            $this->get('session')->getFlashBag()->add('error', 'No tienes permisos para publicar actividades');
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
    
        if(!$request->get('text'))
        {
            $this->get('session')->getFlashBag()->add('error', 'No has escrito ningun texto');
        }
        elseif(!$category && !$request->get('newCategory'))
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe la categoria');
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
                        $subtract = $num_length = strlen((string)$n) + 1 ;
                        $catSlug = substr($slug,0,-$subtract);
                    }
                    
                    $catSlug .= '-'.$n;
                    
                    $n++;
                }
            
                $category->setSlug($catSlug);
                $category->setText('');
            }
            
            $category->setLastchange(new \DateTime('now'));
            $em->persist($category); 
            
            $item->setCategory($category);
            $item->setAuthor($user);
            if($request->get('name'))
            {
                $item->setName($request->get('name'));
            }
            else
            {
                $item->setName(trim($request->get('dateini')).' ('.$request->get('dateinihour').':'.$request->get('dateiniminute').') - '.trim($request->get('dateend')).' ('.$request->get('dateendhour').':'.$request->get('dateendminute').')');
            }
            
            
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
                    $subtract = $num_length = strlen((string)$n) + 1 ;
                    $slug = substr($slug,0,-$subtract);
                }
                
                $slug .= '-'.$n;
                
                $n++;
            }
            $item->setSlug($slug);
            
            $item->summarize($request->get('text'));
            $item->setAllowComments(true);
            $item->setDraft(false);
            $item->setOpen($request->get('open'));
            $item->setPart(false);
            $item->setActivity(null);
            $item->setDateini(new \DateTime(trim($request->get('dateini')).' '.$request->get('dateinitime')));
            $item->setDateend(new \DateTime(trim($request->get('dateend')).' '.$request->get('dateendtime')));
            $item->setAllowassistances(true);
            $item->setText($request->get('text'));
            $item->setDistance(floatval(str_replace(',','.', $request->get('distance'))));
            $item->setUphill(intval($request->get('uphill')));
            $item->setDownhill(0);
            $item->setDifficulty($request->get('difficulty'));
            $item->setStatus('');
            
            if($request->get('attachTo'))
            {
                $itemRelated = $em->getRepository('ColectaItemBundle:Item')->findOneById($request->get('attachTo'));
                $relation = new Relation();
                $relation->setUser($user);
                $relation->setItemto($itemRelated);
                $relation->setItemfrom($item);
                $relation->setText($itemRelated->getName());
                
                $em->persist($relation);
                
                $item->setPart(true);
            }
            
            $em->persist($item); 
            $em->flush(); 
        }
        
        if(isset($item))
        {
            return new RedirectResponse($this->generateUrl('ColectaEventView',array('slug'=>$item->getSlug())));
        }
        else
        {
            $item = new Event();
            $item->setText($request->get('text'));
            $item->setName($request->get('name'));
            $item->setDateini(new \DateTime(trim($request->get('dateini')).' '.$request->get('dateinitime')));
            $item->setDateend(new \DateTime(trim($request->get('dateend')).' '.$request->get('dateendtime')));
            $item->setDistance(str_replace(',','.', $request->get('distance')));
            $item->setUphill($request->get('uphill'));
            $item->setDifficulty($request->get('difficulty'));
            //$item->setAttachTo($request->get('attachTo'));
            
            return $this->render('ColectaItemBundle:Default:newItem.html.twig', array('type' => 'Event', 'item'=>$item));
        }
    }
    public function editAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $request = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if(!$this->getUser() || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaEventView', array('slug'=>$slug)));
        }
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $persist = true;
            
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
        
            if(!$category)
            {
                $this->get('session')->getFlashBag()->add('error', 'No existe la categoria');
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
                            $subtract = $num_length = strlen((string)$n) + 1 ;
                            $catSlug = substr($slug,0,-$subtract);
                        }
                        
                        $catSlug .= '-'.$n;
                        
                        $n++;
                    }
                
                    $category->setSlug($catSlug);
                    $category->setText('');
                    $category->setLastchange(new \DateTime('now'));
                    
                    $em->persist($category); 
                }
                
                $item->setCategory($category);
            }
            
            if(!$request->get('text'))
            {
                $this->get('session')->getFlashBag()->add('error', 'No puedes dejar vacío el texto');
                $persist = false;
            }
            
            $item->setName($request->get('name'));
            $item->summarize($request->get('text'));
            $item->setAllowComments(true);
            $item->setDraft(false);
            $item->setOpen($request->get('open'));
            $item->setActivity(null);
            $item->setDateini(new \DateTime(trim($request->get('dateini')).' '.$request->get('dateinitime')));
            $item->setDateend(new \DateTime(trim($request->get('dateend')).' '.$request->get('dateendtime')));
            //$item->setAllowassistances(true);
            $item->setText($request->get('text'));
            $item->setDistance(str_replace(',','.', $request->get('distance')));
            $item->setUphill($request->get('uphill'));
            $item->setDownhill(0);
            $item->setDifficulty($request->get('difficulty'));
            $item->setStatus('');
            
            if($persist)
            {
                $em->persist($item); 
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Modificado con éxito.');
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        return $this->render('ColectaActivityBundle:Event:edit.html.twig', array('item' => $item, 'categories'=>$categories));
    }
    
    public function deleteAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if(!$item)
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        if(!$this->getUser() || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaEventView', array('slug'=>$slug)));
        }
        
        $name = $item->getName();
        
        $em->remove($item);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('success', '"'.$name.'" ha sido eliminado.');
        
        return new RedirectResponse($this->generateUrl('ColectaDashboard'));
    }
    
    public function assistanceAction($slug)
    /*
        Mark Assistance of logged User to an Event
        
        From the Event node
    */
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if(!$user || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        elseif(!$item)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe el evento');
        }
        else
        {
            $assistance = $em->getRepository('ColectaActivityBundle:EventAssistance')->findOneBy(array('event'=>$item->getId(),'user'=>$user->getId()));
            
            if($assistance)
            {
                $this->get('session')->getFlashBag()->add('error', 'Ya has marcado tu asistencia');
            }
            else
            {
                $assistance = new EventAssistance();
                $assistance->setConfirmed(0);
                $assistance->setKm($item->getDistance());
                $assistance->setUser($user);
                $assistance->setEvent($item);
                
                /* Notification to owner */
                if($user != $item->getAuthor())
                {
                    $notification = $em->getRepository('ColectaUserBundle:Notification')->findOneBy(array('user'=>$item->getAuthor(),'dismiss'=>0,'what'=>'assist','item'=>$item));
                    
                    if($notification)
                    {
                        if($notification->getWho() != $user) 
                        {
                            $notification->setPluspeople(intval($notification->getPluspeople()) + 1);
                        }
                        $notification->setDate(new \DateTime('now'));
                    }
                    else
                    {
                        $notification = new Notification();
                        // text date dismiss user
                        $notification->setUser($item->getAuthor());
                        $notification->setDismiss(0);
                        $notification->setDate(new \DateTime('now'));
                        $notification->setWhat('assist');
                        $notification->setWho($user);
                        $notification->setItem($item);
                        //$notification->setText($user->getName().' va a asistir a :item:'.$item->getId().':');
                    }
                    $em->persist($notification); 
                }
                else
                {
                    if($item->getDateini() < new \DateTime('now'))
                    {
                        $assistance->setConfirmed(1);
                        
                        $points = $this->getPointsHandler($assistance, true);
                        
                        $conditions = $em->getRepository('ColectaUserBundle:PointsCondition')->findBy(array(), array('priority'=>'DESC'));
                        $points->applyConditions($conditions);
                        
                        $em->persist($points);
                    }
                    
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
    
    public function unassistanceAction($slug)
    /*
        Mark Unassistance of logged User to an Event
        
        From the Event node
    */
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if(!$user || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        elseif(!$item)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe el evento');
        }
        else
        {
            $assistance = $em->getRepository('ColectaActivityBundle:EventAssistance')->findOneBy(array('event'=>$item->getId(),'user'=>$user->getId()));
            
            if(!$assistance)
            {
                $this->get('session')->getFlashBag()->add('error', 'No habías marcado tu asistencia');
            }
            else
            {
	            $points = $this->getPointsHandler($assistance, false);
	            
	            if($points)
                {
	                $em->remove($points);
                }
                
                $em->remove($assistance);
                
                $em->flush();
                
                //Notification to the author of the event
                if($user != $item->getAuthor())
                {
                    $notification = $em->getRepository('ColectaUserBundle:Notification')->findOneBy(array('user'=>$item->getAuthor(),'dismiss'=>0,'what'=>'unassist','item'=>$item));
                    
                    if($notification)
                    {
                        if($notification->getWho() != $user) 
                        {
                            $notification->setPluspeople(intval($notification->getPluspeople()) + 1);
                        }
                        $notification->setDate(new \DateTime('now'));
                    }
                    else
                        {
                        $notification = new Notification();
                        $notification->setUser($item->getAuthor());
                        $notification->setDismiss(0);
                        $notification->setDate(new \DateTime('now'));
                        $notification->setWhat('unassist');
                        $notification->setWho($user);
                        $notification->setItem($item);
                        //$notification->setText($user->getName().' ya no asiste a :item:'.$item->getId().':');
                    }
                    
                    $em->persist($notification); 
                }
                
                $this->get('session')->getFlashBag()->add('success', 'Has desmarcado tu asistencia al evento.');
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
    
    public function updateAssistancesAction($slug)
    /*
        Update Assistances
        
        From the menu-list of assistances.
        
        First checks for changes on the list.
        Finally checks for user asssistance by username
    */
    {
        $request = $this->get('request')->request;
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
        
        if(!$user || $user->getRole()->is('ROLE_BANNED'))
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        elseif(!$item)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe el evento');
        }
        elseif(!$item->canEdit($user))
        {
            $this->get('session')->getFlashBag()->add('error', 'No tienes permiso para gestionar las asistencias de este evento');
        }
        else
        {
            if($item->hasAssistances())
            {
                $assistances = $item->getAssistances();
                foreach($assistances as $ass)
                {
                    $id = $ass->getUser()->getId().'';
                    $ass->setKm(str_replace(',','.', $request->get('user'.$id.'km')));
                    
                    if($request->get('user'.$id.'confirmed'))
                    {
                        $ass->setConfirmed(true);
                        
                        $points = $this->getPointsHandler($ass, true);
                        if($request->get('user'.$id.'points') != "")
                        {
                            $points->setPoints(str_replace(',','.', $request->get('user'.$id.'points')));
                        }
                        else
                        {
                            $points->setPoints($ass->getKm());
                            
                            $conditions = $em->getRepository('ColectaUserBundle:PointsCondition')->findBy(array(), array('priority'=>'DESC'));
                            $points->applyConditions($conditions);
                        }
                        
                        
                        $em->persist($points); 
                    }
                    else
                    {
                        $ass->setConfirmed(false);
                        
                        $points = $this->getPointsHandler($ass, false);
                        if($points)
                        {
                            $em->remove($points);
                        }
                    }
                    
                    $em->persist($ass); 
                    $em->flush();
                }
            }
            
            //Add new assistance
            if($request->get('targetUser'))
            {
                //Check if Target User exists
                $targetUser = $em->getRepository('ColectaUserBundle:User')->findOneByName($request->get('targetUser'));
                if(! $targetUser)
                {
                    $this->get('session')->getFlashBag()->add('error', 'No hemos encontrado al usuario que indicas');
                }
                else
                {
                    //Check if assistance already exists for this user
                    $assistance = $em->getRepository('ColectaActivityBundle:EventAssistance')->findOneBy(array('event'=>$item->getId(),'user'=>$targetUser->getId()));
                    
                    if($assistance)
                    {
                        $this->get('session')->getFlashBag()->add('error', $targetUser->getName().' ya está en la lista de asistentes.');
                    }
                    else
                    {
                        $assistance = new EventAssistance();
                        $assistance->setConfirmed(0);
                        $assistance->setKm($item->getDistance());
                        $assistance->setUser($targetUser);
                        $assistance->setEvent($item);
                        
                        $now = new \DateTime('now');
                        //If this is an event that has already happened
                        if($item->getDateend() <= $now) 
                        {
                            $assistance->setConfirmed(1); //Confirm the assistance
                            
                            //set points for assistance
                            $points = $this->getPointsHandler($assistance, true);
                            $points->setPoints($item->getDistance());
                            
                            $conditions = $em->getRepository('ColectaUserBundle:PointsCondition')->findBy(array(), array('priority'=>'DESC'));
                            $points->applyConditions($conditions);
                            
                            $em->persist($points); 
                        }
                        
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
    public function assistanceConfirmAction($slug, $user_id) //JSON Method
    {
	    $em = $this->getDoctrine()->getManager();
	    
	    $user = $this->getUser();
	    
	    if(!$user || $this->getUser()->getRole()->is('ROLE_BANNED'))
	    {
		    //Must be logged
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('error' => 'Tienes que iniciar sesión','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
	    
	    if(!$item)
	    {
		    //no item found
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('error' => 'No se ha encontrado el evento','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    elseif(!$item->canEdit($user))
	    {
		    //you cannot edit this item
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('error' => 'No tienes los permisos suficientes','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $targetUser = $em->getRepository('ColectaUserBundle:User')->findOneById($user_id);
	    
	    if(!$targetUser)
	    {
		    //target user not found
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('error' => 'No se ha encontrado el usuario','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $assistance = $em->getRepository('ColectaActivityBundle:EventAssistance')->findOneBy(array('event'=>$item->getId(),'user'=>$targetUser->getId()));
                    
        if(!$assistance)
        {
	        $assistance = new EventAssistance();
            $assistance->setUser($targetUser);
            $assistance->setEvent($item);
        }
        
        $assistance->setConfirmed(true);
        $assistance->setKm($item->getDistance());
            
        $points = $this->getPointsHandler($assistance, true);
        $points->setPoints($assistance->getKm());
        
        $conditions = $em->getRepository('ColectaUserBundle:PointsCondition')->findBy(array(), array('priority'=>'DESC'));
        $points->applyConditions($conditions);
        
        $em->persist($assistance);
        $em->persist($points);
        
        $em->flush();
        
        $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('user_id' => $user_id, 'error' => '','success' => $points->getPoints() . ' puntos | ' . $assistance->getKm() . ' km', 'points' => $points->getPoints(), 'km' => $assistance->getKm())),200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

	public function assistanceRemoveAction($slug, $user_id) //JSON Method
    {
	    $em = $this->getDoctrine()->getManager();
	    
	    $user = $this->getUser();
	    
	    if(!$user || $this->getUser()->getRole()->is('ROLE_BANNED'))
	    {
		    //Must be logged
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('error' => 'Tienes que iniciar sesión','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
	    
	    if(!$item)
	    {
		    //no item found
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('error' => 'No se ha encontrado el evento','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    elseif(!$item->canEdit($user))
	    {
		    //you cannot edit this item
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('error' => 'No tienes los permisos suficientes','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $targetUser = $em->getRepository('ColectaUserBundle:User')->findOneById($user_id);
	    
	    if(!$targetUser)
	    {
		    //target user not found
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('error' => 'No se ha encontrado el usuario','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $assistance = $em->getRepository('ColectaActivityBundle:EventAssistance')->findOneBy(array('event'=>$item->getId(),'user'=>$targetUser->getId()));
        
        $points = $this->getPointsHandler($assistance, false);
	            
        if($points)
        {
            $em->remove($points);
        }
               
        if($assistance)
        {
            $em->remove($assistance);
        }
        
        $em->flush();
        
        $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('user_id' => $user_id, 'error' => '','success' => 'Asistencia eliminada')),200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function assistanceUpdateAction($slug, $user_id) //JSON Method
    {
	    $em = $this->getDoctrine()->getManager();
	    $request = $this->get('request')->request;
	    
	    $user = $this->getUser();
	    
	    if(!$user || $this->getUser()->getRole()->is('ROLE_BANNED'))
	    {
		    //Must be logged
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('user_id' => $user_id, 'error' => 'Tienes que iniciar sesión','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $item = $em->getRepository('ColectaActivityBundle:Event')->findOneBySlug($slug);
	    
	    if(!$item)
	    {
		    //no item found
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('user_id' => $user_id, 'error' => 'No se ha encontrado el evento','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    elseif(!$item->canEdit($user))
	    {
		    //you cannot edit this item
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('user_id' => $user_id, 'error' => 'No tienes los permisos suficientes','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $targetUser = $em->getRepository('ColectaUserBundle:User')->findOneById($user_id);
	    
	    if(!$targetUser)
	    {
		    //target user not found
		    $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('user_id' => $user_id, 'error' => 'No se ha encontrado el usuario','success' => '')),200);
	        $response->headers->set('Content-Type', 'application/json');
	        return $response;
	    }
	    
	    $assistance = $em->getRepository('ColectaActivityBundle:EventAssistance')->findOneBy(array('event'=>$item->getId(),'user'=>$targetUser->getId()));
                    
        if(!$assistance)
        {
	        $assistance = new EventAssistance();
            $assistance->setConfirmed(1);
            $assistance->setUser($targetUser);
            $assistance->setEvent($item);
        }
        
        $assistance->setConfirmed(true);
        
        $assistance->setKm(floatval(str_replace(',','.', $request->get('user'.$user_id.'km'))));
            
        $points = $this->getPointsHandler($assistance, true);
        $points->setPoints(intval(str_replace(',','.', $request->get('user'.$user_id.'points'))));
        
        $em->persist($assistance);
        $em->persist($points);
        
        $em->flush();
        
        $response = new Response($this->renderView('ColectaActivityBundle:Event:response.json.twig', array('user_id' => $user_id, 'error' => '','success' => $points->getPoints() . ' puntos | ' . $assistance->getKm() . ' km', 'points' => $points->getPoints(), 'km' => $assistance->getKm())),200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    protected function getPointsHandler(\Colecta\ActivityBundle\Entity\EventAssistance $assistance, $createIfNotExists = false)
    {
        $em = $this->getDoctrine()->getManager();
        
        $pointsResult = $em->getRepository('ColectaUserBundle:Points')->findBy(array('user'=>$assistance->getUser(), 'item'=>$assistance->getEvent()));
        
        if(count($pointsResult) == 0)
        {
            if($createIfNotExists)
            {
                $points = new Points();
                $points->setUser($assistance->getUser());
                $points->setItem($assistance->getEvent());
                $points->setDate(new \DateTime('now'));
                
                return $points;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return $pointsResult[0];
        }
    }
    
    public function calendarAction($date)
    {
        $dateOb = new \DateTime($date);
        $em = $this->getDoctrine()->getManager();
        
        $SQLprivacy = '';
        
        if(!$this->getUser() || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $SQLprivacy = ' AND e.open = 1 ';
        }
        
        $items = $em->createQuery('SELECT e FROM ColectaActivityBundle:Event e WHERE e.draft = 0 '.$SQLprivacy.' AND e.dateini <= \''.$dateOb->format('Y-m-t 23:59:59').'\' AND e.dateend >= \''.$dateOb->format('Y-m-1 00:00:00').'\' ORDER BY e.dateini ASC')->getResult();
        
        //array of each day of the month prepared with empty arrays
        $ev = array();
        for($i = 1; $i <= 31; $i++){$ev[$i] = array();}
        
        //fulfill the days with existing events
        if(count($items))
        {
            foreach($items as $item)
            {
                if($item->getDateini()->format('m') != $dateOb->format('m')) //the event starts before this month
                {
                    $dayIni = 1;
                }
                else
                {
                    $dayIni = intval($item->getDateini()->format('j'));
                }
                
                if($item->getDateend()->format('m') != $dateOb->format('m')) //the event ends after this month
                {
                    $dayEnd = $dateOb->format('t'); //last day of current month
                }
                else
                {
                    $dayEnd = intval($item->getDateend()->format('j'));
                }
                
                //Fullfill days of the month
                for($i = $dayIni; $i <= $dayEnd; $i++)
                {
                    $ev[$i][] = $item;
                }
            }
        }
        
        return $this->render('ColectaActivityBundle:Event:calendar.html.twig', array('events' => $ev, 'targetdate'=>$date, 'targetdateob'=>$dateOb));
    }
    
    public function icsAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $SQLprivacy = '';
        
        if(!$this->getUser() || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $SQLprivacy = ' AND e.open = 1 ';
        }
        
        $items = $em->createQuery('SELECT e FROM ColectaActivityBundle:Event e WHERE e.draft = 0 '.$SQLprivacy.' ORDER BY e.dateini DESC')->setMaxResults(100)->getResult();
        
        return $this->render('ColectaActivityBundle:Event:calendar.ics.twig', array('events' => $items));
    }
    
    public function nextEventAction()
    {
        $now = new \DateTime();
        $em = $this->getDoctrine()->getManager();
        $items = $em->createQuery('SELECT e FROM ColectaActivityBundle:Event e WHERE e.draft = 0 AND e.dateini >= \''.$now->format('Y-m-d H:i:s').'\' ORDER BY e.dateini ASC')->setMaxResults(3)->getResult();
        
        return $this->render('ColectaActivityBundle:Event:nextEvent.html.twig', array('events' => $items));
    }
    
    public function abouticsAction()
    {
        return $this->render('ColectaActivityBundle:Event:aboutics.html.twig');
    }
}
