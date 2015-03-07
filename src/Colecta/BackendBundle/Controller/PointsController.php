<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\UserBundle\Entity\PointsCondition;

class PointsController extends Controller
{
    public function indexAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $pointsConditions = $em->getRepository('ColectaUserBundle:PointsCondition')->findBy(array(),array('priority'=>'DESC'));
        
        $roles_keys = array();
        $roles = $em->getRepository('ColectaUserBundle:Role')->findAll();
        
        foreach($roles as $role)
        {
            $roles_keys[$role->getId()] = $role->getDescription();
        }
        
        $categories_keys = array();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        foreach($categories as $category)
        {
            $categories_keys[$category->getId()] = $category->getName();
        }
        
        return $this->render('ColectaBackendBundle:Points:index.html.twig', array('pointsConditions' => $pointsConditions, 'roles_keys' => $roles_keys, 'categories_keys' => $categories_keys));
    }
    
    public function newCaseAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $pointsCondition = new PointsCondition();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {   
            $request = $this->get('request')->request;
            
            $pointsCondition->setName($request->get('pointsName') ?: '');
            $pointsCondition->setOperator($request->get('pointsOperator') ?: '+');
            $pointsCondition->setValue($request->get('pointsValue') ?: 0);
            $pointsCondition->setGather($request->get('pointsGather') == '1' ? true : false);
            $pointsCondition->setPriority($request->get('pointsPriority') ?: 0);
            $pointsCondition->setAuthor($this->getUser());
            
            $condition = 0;
            $requirement = array();
            while($request->get('pointsCond'. $condition))
            {
                $requirement[$condition]['condition'] = $request->get('pointsCond'. $condition);
                $requirement[$condition]['value'] = $request->get('pointsCond'. $condition .'Value') ?: 0;
                
                $condition++;
            }
            
            $pointsCondition->setRequirement($requirement);
            
            $em->persist($pointsCondition);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'Caso guardado');
                
            return new RedirectResponse($this->generateUrl('ColectaBackendPointsCase', array('case_id'=>$pointsCondition->getId()))); //Case edit page
        }
        
        $roles_array = array();
        $roles = $em->getRepository('ColectaUserBundle:Role')->findAll();
        
        foreach($roles as $role)
        {
            $roles_array[] = array($role->getId(), $role->getDescription());
        }
        
        $categories_array = array();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        foreach($categories as $category)
        {
            $categories_array[] = array($category->getId(), $category->getName());
        }
        
        return $this->render('ColectaBackendBundle:Points:pointsEdit.html.twig', array('pointsCondition' => $pointsCondition, 'roles_array' => $roles_array, 'categories_array' => $categories_array));
    }
    
    public function editCaseAction($case_id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $pointsCondition = $em->getRepository('ColectaUserBundle:PointsCondition')->findOneById($case_id);
        
        if(!$pointsCondition)
        {
            $this->get('session')->getFlashBag()->add('error', 'No se ha podido recuperar el caso.');
            return new RedirectResponse($this->generateUrl('ColectaBackendPointsIndex'));
        }
        
        if ($this->get('request')->getMethod() == 'POST') 
        {   
            $request = $this->get('request')->request;
            
            $pointsCondition->setName($request->get('pointsName') ?: '');
            $pointsCondition->setOperator($request->get('pointsOperator') ?: '+');
            $pointsCondition->setValue($request->get('pointsValue') ?: 0);
            $pointsCondition->setGather($request->get('pointsGather') == '1' ? true : false);
            $pointsCondition->setPriority($request->get('pointsPriority') ?: 0);
            $pointsCondition->setAuthor($this->getUser());
            
            $condition = 0;
            $requirement = array();
            while($request->get('pointsCond'. $condition))
            {
                $requirement[$condition]['condition'] = $request->get('pointsCond'. $condition);
                $requirement[$condition]['value'] = $request->get('pointsCond'. $condition .'Value') ?: 0;
                
                $condition++;
            }
            
            $pointsCondition->setRequirement($requirement);
            
            $em->persist($pointsCondition);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'Caso guardado');
                
            return new RedirectResponse($this->generateUrl('ColectaBackendPointsCase', array('case_id'=>$pointsCondition->getId()))); //Case edit page
        }
        
        $roles_array = array();
        $roles = $em->getRepository('ColectaUserBundle:Role')->findAll();
        
        foreach($roles as $role)
        {
            $roles_array[] = array($role->getId(), $role->getDescription());
        }
        
        $categories_array = array();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        foreach($categories as $category)
        {
            $categories_array[] = array($category->getId(), $category->getName());
        }
        
        return $this->render('ColectaBackendBundle:Points:pointsEdit.html.twig', array('pointsCondition' => $pointsCondition, 'roles_array' => $roles_array, 'categories_array' => $categories_array));
    }
    
    public function deleteCaseAction($case_id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $pointsCondition = $em->getRepository('ColectaUserBundle:PointsCondition')->findOneById($case_id);
        
        if(!$pointsCondition)
        {
            $this->get('session')->getFlashBag()->add('error', 'No se ha podido recuperar el caso.');
            return new RedirectResponse($this->generateUrl('ColectaBackendPointsIndex'));
        }
        else
        {
            $em->remove($pointsCondition); 
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Caso eliminado correctamente'); 
        }
        
        return new RedirectResponse($this->generateUrl('ColectaBackendPointsIndex'));
    }
}