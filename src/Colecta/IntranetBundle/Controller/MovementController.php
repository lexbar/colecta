<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\IntranetBundle\Entity\Movement;

class MovementController extends Controller
{
    
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $movements = $em->createQuery('SELECT m FROM ColectaIntranetBundle:Movement m ORDER BY m.date ASC')->getResult();
        $categories = $em->getRepository('ColectaIntranetBundle:MovementCategory')->findAll();
        
        return $this->render('ColectaIntranetBundle:Movement:index.html.twig', array('movements'=>$movements, 'categories'=>$categories));
    }
    public function addAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $category = $em->getRepository('ColectaIntranetBundle:MovementCategory')->findOneById($request->get('category'));
    
        if(!$user) 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif(!$request->get('date') OR !$request->get('amount') OR !$request->get('concept'))
        {
            $this->get('session')->setFlash('error', 'Debes rellenar todos los datos');
        }
        elseif(!$category)
        {
            $this->get('session')->setFlash('error', 'No existe la categoria');
        }
        else
        {
            $displace  = floatval(str_replace(',','.',$request->get('amount')));
            $date = new \DateTime($request->get('date'));
            
            $previousmovement = $em->createQuery('SELECT m FROM ColectaIntranetBundle:Movement m WHERE m.date <= \''.$date->format('Y-m-d H:i:s').'\' ORDER BY m.date DESC')->setMaxResults(1)->getResult(); 
            
            if(count($previousmovement))
            {
                $balance = floatval($previousmovement[0]->getBalance()) + $displace;
            }
            else
            {
                $balance = $displace;
            }
            
            $movement = new Movement();
            $movement->setMovementCategory($category);
            $movement->setUser($user);
            $movement->setDate($date);
            $movement->setAmount($displace);
            $movement->setConcept($request->get('concept'));
            $movement->setBalance($balance);
            
            $em->persist($movement); 
            
            $nextmovements = $em->createQuery('SELECT m FROM ColectaIntranetBundle:Movement m WHERE m.date > \''.$date->format('Y-m-d H:i:s').'\'')->getResult(); 
            
            if(count($nextmovements))
            {
                foreach($nextmovements as $mov)
                {
                    $mov->setBalance($mov->getBalance() + $displace);
                    $em->persist($mov);
                }
            }
            
            $em->flush();
        }
        
        return new RedirectResponse($this->generateUrl('ColectaMovementIndex'));
    }
}
