<?php

namespace Colecta\ColectiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ColectiveBundle\Entity\Poll;
use Colecta\ColectiveBundle\Entity\PollOption;

class PollController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaColectiveBundle:Poll')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();

        return $this->render('ColectaColectiveBundle:Poll:index.html.twig', array('items' => $items, 'categories' => $categories));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaColectiveBundle:Poll')->findOneBySlug($slug);
        
        return $this->render('ColectaColectiveBundle:Poll:full.html.twig', array('item' => $item));
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
            $poll= new Poll();
            $poll->setCategory($category);
            $poll->setAuthor($user);
            $poll->setName($request->get('name'));
            $poll->setSlug(str_replace(' ', '_', strtolower($request->get('name'))));
            $poll->setSummary( substr($request->get('description'),0,255) );
            $poll->setTagwords( substr($request->get('description'),255,255) );
            $poll->setDate(new \DateTime('now'));
            $poll->setAllowComments(true);
            $poll->setDraft(false);
            $poll->setDescription($request->get('description'));
            $poll->setEndDate(new \DateTime('now'));
            
            $n = 0;
            
            while($request->get('option'.$n))
            {
                $option = new PollOption();
                $option->setText($request->get('option'.$n));
                $option->setPoll($poll);
                
                $poll->addPollOption($option);
                $em->persist($option); 
                
                $n++;
            }
            
            $em->persist($poll); 
            $em->flush();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaPollIndex');
        }
        
        return new RedirectResponse($referer);
    }
}
