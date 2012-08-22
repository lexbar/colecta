<?php

namespace Colecta\ColectiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ColectiveBundle\Entity\Contest;
use Colecta\ColectiveBundle\Entity\ContestWinner;

class ContestController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaColectiveBundle:Contest')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();

        return $this->render('ColectaColectiveBundle:Contest:index.html.twig', array('items' => $items, 'categories' => $categories));
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
                    $slug = substr($slug,-2,2);
                }
                
                $slug .= '_'.$n;
                
                $n++;
            }
            $contest->setSlug($slug);
            
            $contest->setSummary( substr($request->get('description'),0,255) );
            $contest->setTagwords( substr($request->get('description'),255,255) );
            $contest->setDate(new \DateTime('now'));
            $contest->setAllowComments(true);
            $contest->setDraft(false);
            $contest->setDescription($request->get('description'));
            $contest->setIniDate(new \DateTime('now'));
            $contest->setEndDate(new \DateTime('now'));
            $contest->setItemTypes('');
            
            $n = 0;
            
            while($request->get('position'.$n))
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
