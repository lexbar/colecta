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
        
        $em = $this->getDoctrine()->getManager();
        
        //Get ALL the posts and folders that are not drafts
        $items = $em->createQuery("SELECT i FROM ColectaItemBundle:Item i WHERE (i INSTANCE OF Colecta\ItemBundle\Entity\Post OR i INSTANCE OF Colecta\FilesBundle\Entity\Folder) AND i.draft = 0 ORDER BY i.date DESC")->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1)->getResult();
        //$items = $em->getRepository('ColectaItemBundle:Post')->findBy(array('draft'=>0), array('lastInteraction'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
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
        $em = $this->getDoctrine()->getManager();
        
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
        $em = $this->getDoctrine()->getManager();
        $request = $this->get('request')->request;
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
        
        if($user == 'anon.') 
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
        }
        elseif(!$request->get('name'))
        {
            $this->get('session')->getFlashBag()->add('error', 'Debes escribir un título');
        }
        elseif(!$request->get('text'))
        {
            $this->get('session')->getFlashBag()->add('error', 'No has escrito ningún texto');
        }
        elseif(!$request->get('newCategory') && !$category)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe la categoria');
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
            $item->setPart(false);
            $item->setText($request->get('text'));
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
                if(isset($linkPreview['images'])) 
                { 
                    $images = explode('|', $linkPreview['images']) ;
                }
                else
                {
                    $images = array('');
                }
                
                $item->setLinkURL($linkPreview['pageUrl']);
                $item->setLinkImage($images[0]);
                $item->setLinkTitle($linkPreview['title']);
                $item->setLinkExcerpt(substr($linkPreview['description'], 0, 240));
            }
            
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
            return new RedirectResponse($this->generateUrl('ColectaPostView',array('slug'=>$item->getSlug())));
        }
        else
        {
            $item = new Post();
            $item->setText($request->get('text'));
            $item->setName($request->get('name'));
            
            return $this->render('ColectaItemBundle:Default:newItem.html.twig', array('type' => 'Post', 'item'=>$item));
        }
    }
    public function editAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
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
                if(isset($linkPreview['images'])) 
                { 
                    $images = explode('|', $linkPreview['images']) ;
                }
                else
                {
                    $images = array('');
                }
                
                $item->setLinkURL($linkPreview['pageUrl']);
                $item->setLinkImage($images[0]);
                $item->setLinkTitle($linkPreview['title']);
                $item->setLinkExcerpt(substr($linkPreview['description'], 0, 240));
            }
            
            if($persist)
            {
                $em->persist($item); 
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Modificado con éxito.');
            }
        }
        
        return $this->render('ColectaItemBundle:Post:edit.html.twig', array('item' => $item));
    }
    
    public function deleteAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
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
        
        $this->get('session')->getFlashBag()->add('success', '"'.$name.'" ha sido eliminado.');
        
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