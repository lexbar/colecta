<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ItemBundle\Entity\Post;
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
        $items = $em->getRepository('ColectaItemBundle:Post')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
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
        
        return $this->render('ColectaItemBundle:Post:index.html.twig', array('items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaItemBundle:Post')->findOneBySlug($slug);
        
        return $this->render('ColectaItemBundle:Post:full.html.twig', array('item' => $item));
    }
    public function newAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaItemBundle:Post:new.html.twig', array('categories' => $categories));
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
            $post = new Post();
            
            if($request->get('newCategory'))
            {
                $category = new Category();
                $category->setName($request->get('newCategory'));
                
                //Category Slug generate
                $catSlug = $post->generateSlug($request->get('newCategory'));
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
                
                $post->setCategory($category);
            }
            
            $post->setCategory($category);
            $post->setAuthor($user);
            $post->setName($request->get('name'));
            
            //Slug generate
            if(strlen($request->get('name')) == 0)
            {
                $slug = 'untitled';
            }
            else
            {
                $slug = $post->generateSlug();
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
            $post->setSlug($slug);
            
            $post->summarize($request->get('text'));
            $post->setAllowComments(true);
            $post->setDraft(false);
            $post->setText($request->get('text'));
            
            
            $em->persist($post); 
            $em->flush();
        }
        
        if(isset($post))
        {
            return new RedirectResponse($this->generateUrl('ColectaPostView',array('slug'=>$post->getSlug())));
        }
        else
        {
            $this->get('session')->setFlash('PostName', $request->get('name'));
            $this->get('session')->setFlash('PostText', $request->get('text'));
            return new RedirectResponse($this->generateUrl('ColectaPostNew'));
        }
    }
    public function editAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaItemBundle:Post')->findOneBySlug($slug);
        
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
                $item->setCategory($category);
            }
            
            if(!$request->get('name'))
            {
                $this->get('session')->setFlash('error', 'No puedes dejar vacío el nombre');
                $persist = false;
            }
            
            $item->setName($request->get('name'));
            $item->setText($request->get('text'));
            $item->summarize($request->get('text'));
            
            if($persist)
            {
                $em->persist($item); 
                $em->flush();
                $this->get('session')->setFlash('success', 'Modificado con éxito.');
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        return $this->render('ColectaItemBundle:Post:edit.html.twig', array('item' => $item, 'categories'=>$categories));
    }
}
