<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DomCrawler\Crawler;
use Colecta\ItemBundle\Entity\Post;
use Colecta\ItemBundle\Entity\Relation;
use Colecta\ItemBundle\Entity\Category;

class PostController extends Controller
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
        $items = $em->getRepository('ColectaItemBundle:Post')->findBy(array('draft'=>0), array('lastInteraction'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
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
        
        return $this->render('ColectaItemBundle:Post:index.html.twig', array('items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaItemBundle:Post')->findOneBySlug($slug);
        
        return $this->render('ColectaItemBundle:Post:full.html.twig', array('item' => $item));
    }
    public function newAction()
    {        
        return $this->render('ColectaItemBundle:Default:newItem.html.twig', array('type' => 'Post'));
    }
    public function createAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
        
        if($user == 'anon.') 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif(!$request->get('name'))
        {
            $this->get('session')->setFlash('error', 'Debes escribir un título');
        }
        elseif(!$request->get('text'))
        {
            $this->get('session')->setFlash('error', 'No has escrito ningun texto');
        }
        elseif(!$request->get('newCategory') && !$category)
        {
            $this->get('session')->setFlash('error', 'No existe la categoria');
        }
        else
        {
            $item = new Post();
            
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
                $category->setText('');    
            }
            
            $category->setLastchange(new \DateTime('now'));
            $em->persist($category); 
            
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
                
                $slug .= '-'.$n;
                
                $n++;
            }
            $item->setSlug($slug);
            
            $item->summarize($request->get('text'));
            $item->setAllowComments(true);
            $item->setDraft(false);
            $item->setPart(false);
            $item->setText($request->get('text'));
            $item->setLinkURL('');
            $item->setLinkImage('');
            $item->setLinkExcerpt('');
            $item->setLinkTitle('');
            
            $linkPreview = (stripcslashes($this->forward('ColectaItemBundle:linkPreview:textCrawler', array(
                'imagequantity'  => 0,
                'text' => $item->getText(),
            ))->getContent()));
            
            if($linkPreview && $linkPreview[0] == '"')
            {
                $linkPreview = substr($linkPreview, 1, -1);
            }
            
            $linkPreview = json_decode($linkPreview, true);
            
            if($linkPreview && isset($linkPreview['pageUrl'])) 
            {
                $item->setLinkURL($linkPreview['pageUrl']);
                $item->setLinkImage($linkPreview['images']);
                $item->setLinkTitle($linkPreview['title']);
                $item->setLinkExcerpt($linkPreview['description']);
                
            }
            
            /*
            if(preg_match("/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $request->get('text'), $url)) 
            {
                $html = getContent($url[0]);
                //$crawler = new Crawler($html);
                
                $item->setLinkURL($url[0]);
                
                $title = preg_match('!<title>(.*?)</title>!i', $html, $matches) ? $matches[1] : '';
                if($title)
                {
                    $item->setLinkTitle($title);
                }
                
                $excerpt = preg_match('!<meta .*name="description" .*content="(.*)"!i', $html, $matches) ? $matches[1] : '';
                if($excerpt)
                {
                    $item->setLinkExcerpt($excerpt);
                }
            }*/
            
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
            return new RedirectResponse($this->generateUrl('ColectaPostView',array('slug'=>$item->getSlug())));
        }
        else
        {
            $this->get('session')->setFlash('PostName', $request->get('name'));
            $this->get('session')->setFlash('PostText', $request->get('text'));
            $this->get('session')->setFlash('PostCategory', $request->get('category'));
            return new RedirectResponse($this->generateUrl('ColectaPostNew'));
        }
    }
    public function editAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaItemBundle:Post')->findOneBySlug($slug);
        
        if($user == 'anon.' || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaPostView', array('slug'=>$slug)));
        }
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
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
                    $category->setText('');
                    $category->setLastchange(new \DateTime('now'));
                    
                    $em->persist($category); 
                }
                
                $item->setCategory($category);
            }
            
            if(!$request->get('text'))
            {
                $this->get('session')->setFlash('error', 'No puedes dejar vacío el texto');
                $persist = false;
            }
            
            $item->setName($request->get('name'));
            $item->setText($request->get('text'));
            $item->summarize($request->get('text'));
            $item->setLinkURL('');
            $item->setLinkImage('');
            $item->setLinkExcerpt('');
            $item->setLinkTitle('');
            
            $linkPreview = (stripcslashes($this->forward('ColectaItemBundle:linkPreview:textCrawler', array(
                'imageQuantity'  => 0,
                'text' => $item->getText(),
            ))->getContent()));
            
            if($linkPreview && $linkPreview[0] == '"')
            {
                $linkPreview = substr($linkPreview, 1, -1);
            }
            
            $linkPreview = json_decode($linkPreview, true);
            
            if($linkPreview && isset($linkPreview['pageUrl'])) 
            {
                $item->setLinkURL($linkPreview['pageUrl']);
                $item->setLinkImage($linkPreview['images']);
                $item->setLinkTitle($linkPreview['title']);
                $item->setLinkExcerpt($linkPreview['description']);
                
            }
            
            /*if(preg_match("/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $request->get('text'), $url)) 
            {
                $html = getContent($url[0]);
                //$crawler = new Crawler($html);
                
                $item->setLinkURL($url[0]);
                
                $title = preg_match('!<title>(.*?)</title>!i', $html, $matches) ? $matches[1] : '';
                if($title)
                {
                    $item->setLinkTitle($title);
                }
                
                $excerpt = preg_match('!<meta .*name="description" .*content="(.*)"!i', $html, $matches) ? $matches[1] : '';
                if($excerpt)
                {
                    $item->setLinkExcerpt($excerpt);
                }
            }*/
            
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
        
        return $this->render('ColectaItemBundle:Post:edit.html.twig', array('item' => $item));
    }
    
    public function deleteAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaItemBundle:Post')->findOneBySlug($slug);
        
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
}

function getContent($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$content = curl_exec($ch); //return result 
	
	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 403) {
		return null;
	}
	
	if (curl_errno($ch)) {
		return null; //this stops the execution under a Curl failure
	}
	
	curl_close($ch); //close connection_aborted()
	
	return $content;
}