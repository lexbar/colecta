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
            
            //Slug generate
            $slug = $poll->generateSlug();
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
            $poll->setSlug($slug);
            
            $poll->summarize($request->get('description'));
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
    public function voteAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $poll = $em->getRepository('ColectaColectiveBundle:Poll')->findOneBySlug($slug);
        $pollOption = $em->getRepository('ColectaColectiveBundle:PollOption')->find($request->get('option'));
        
        if(!$user) 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif(!$request->get('option'))
        {
            $this->get('session')->setFlash('error', 'No has seleccionado una opción');
        }
        elseif(!$poll || !$pollOption)
        {
            $this->get('session')->setFlash('error', 'No existe la encuesta que quieres votar');
        }
        else
        {
            //Check if the user has voted
            $hasVoted = false;
            
            $options = $poll->getOptions();
            if(count($options))
            {
                foreach($options as $option) {
                    $votes = $option->getVotes();
                    
                    if(count($votes))
                    {
                        foreach($votes as $vote) 
                        {
                            if($vote == $user) 
                            {
                                $hasVoted = true;
                                break;
                            }
                        }
                        
                        if($hasVoted)
                        {
                            break;
                        }
                    }
                }
            }
            
            
            if($hasVoted)
            {
                $this->get('session')->setFlash('error', 'Ya has votado en esta encuesta');
            }
            else
            {
                $pollOption->addUser($user);
                $em->persist($pollOption); 
                $em->flush();
            }
        }
        
        $referer = $this->generateUrl('ColectaPollView',array( 'slug' => $poll->getSlug() ));
        
        return new RedirectResponse($referer);
    }
}
