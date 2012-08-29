<?php

namespace Colecta\ColectiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ColectiveBundle\Entity\Contest;
use Colecta\ColectiveBundle\Entity\ContestWinner;

class ContestController extends Controller
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
        $items = $em->getRepository('ColectaColectiveBundle:Contest')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
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
        
        return $this->render('ColectaColectiveBundle:Contest:index.html.twig', array('items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaColectiveBundle:Contest')->findOneBySlug($slug);
        
        return $this->render('ColectaColectiveBundle:Contest:full.html.twig', array('item' => $item));
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
            $contest= new Contest();
            $contest->setCategory($category);
            $contest->setAuthor($user);
            $contest->setName($request->get('name'));
            
            //Slug generate
            $slug = $contest->generateSlug();
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
            $contest->setSlug($slug);
            
            $contest->summarize($request->get('description'));
            $contest->setAllowComments(true);
            $contest->setDraft(false);
            $contest->setDescription($request->get('description'));
            $contest->setIniDate(new \DateTime('now'));
            $contest->setEndDate(new \DateTime('now'));
            $contest->setItemTypes('');
            
            $n = 0;
            
            //CAMBIARLO POR UN FOR Y UN PARAMETRO NUM DESDE EL FORMULARIO
            while($request->get('position'.$n.'text'))
            {
                $victory = new ContestWinner();
                $victory->setPosition($request->get('position'.$n));
                $victory->setText($request->get('position'.$n.'text'));
                $victory->setContest($contest);
                $victory->setItem(null);
                $victory->setUser(null);
                
                $contest->addContestWinner($victory);
                $em->persist($victory); 
                
                $n++;
            }
            
            $em->persist($contest); 
            $em->flush();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaContestIndex');
        }
        
        return new RedirectResponse($referer);
    }
}
