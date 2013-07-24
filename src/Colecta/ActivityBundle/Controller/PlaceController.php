<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ActivityBundle\Entity\Place;
use Colecta\ItemBundle\Entity\Category;
use Colecta\ItemBundle\Entity\Relation;
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
        elseif(!$category && !$request->get('newCategory'))
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
                    
                    $catSlug .= '-'.$n;
                    
                    $n++;
                }
            
                $category->setSlug($catSlug);
                $category->setDescription('');
                $category->setLastchange(new \DateTime('now'));
                
                $em->persist($category); 
            }
            
            $item->setCategory($category);
            $item->setAuthor($user);
            if(strlen($request->get('name')) == 0)
            {
                $item->setName($this->guessName($request->get('latitude'), $request->get('longitude')));
            }
            else
            {
                $item->setName($request->get('name'));
            }
            
            
            //Slug generate
            $slug = $item->generateSlug();
            $n = 2;
            
            while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
            {
                if($n > 2)
                {
                    $slug = substr($slug,0,-2);
                }
                
                $slug .= '-'.$n;
                
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
            
            // Update all categories. 
            // This is done this way because I'm lazy and so that every time an item is created or modified consistency is granted.
        
            $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
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
        
        if($user == 'anon.' || !$item->canEdit($user)) 
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
                        
                        $catSlug .= '-'.$n;
                        
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
                
                // Update all categories. 
                // This is done this way because I'm lazy and so that every time an item is created or modified consistency is granted.
            
                $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        return $this->render('ColectaActivityBundle:Place:edit.html.twig', array('item' => $item, 'categories'=>$categories));
    }
    
    public function deleteAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Place')->findOneBySlug($slug);
        
        if(!$item)
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        if($user == 'anon.' || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaPostView', array('slug'=>$slug)));
        }
        
        $name = $item->getName();
        
        $em->remove($item);
        $em->flush();
        
        $this->get('session')->setFlash('success', '"'.$name.'" ha sido eliminado.');
        
        return new RedirectResponse($this->generateUrl('ColectaDashboard'));
    }
    
    public function guessName($lat, $lng)
    {
        $content = mycurl('http://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&sensor=false');
        
        $json = json_decode($content, 1);
        
        if(count($json['results']))
        {
            $firstresult = $json['results'][0]['address_components'][0]['long_name'];
            
            foreach($firstresult as $r)
            {
                if(in_array('political',$r['types']))
                {
                    return $r['long_name'];
                }
            }
            
            return $firstresult[0]['long_name'];
        }
        else
        {
            return '';
        }
    }
}

function mycurl($url) 
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 7);
	$content = curl_exec($ch); //return result 
	
	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 403) 
	{
		return null;
	}
	
	if (curl_errno($ch)) 
	{
		return false; //this stops the execution under a Curl failure
	}
	
	curl_close($ch); //close connection_aborted()
	
	return $content;
}