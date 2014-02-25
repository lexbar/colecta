<?php

namespace Colecta\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $page = $em->getRepository('ColectaSiteBundle:Page')->findOneBySlug($slug);
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        /* Get the user role id, if annonymous role id = 0 */
        if($user == 'anon.')
        {
            $role = 0;
        }
        else
        {
            $role = $user->getRole()->getId();
        }
        
        /* If the user role is allowed to access the page */
        if(in_array($role, $page->getTargetRoles()))
        {
            
            return $this->render('ColectaSiteBundle:Page:view.html.twig', array('page' => $page));
        }
        else
        {
            throw $this->createNotFoundException('No tienes permisos para visualizar la página.');
        }
    }
    
    public function navigationAction($context)
    {
        $em = $this->getDoctrine()->getEntityManager();
        if($this->get('security.context')->getToken())
        {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        else
        {
            $user = 'anon.';
        }
        
        //Get all the pages that are not draftsand are ment to be on the sidebar
        $pages = $em->getRepository('ColectaSiteBundle:Page')->findBy(array('draft'=>0, 'sidebarShow'=>1, 'context'=>$context), array('sidebarOrder'=>'ASC'));
        
        if($user == 'anon.')
        {
            $role = 0;
        }
        else
        {
            $role = $user->getRole()->getId();
        }
        
        $pagesFiltered = array();
        
        foreach($pages as $page)
        {
            if(in_array($role, $page->getTargetRoles()))
            {
                $pagesFiltered[] = $page;
            }
        }
        
        return $this->render('ColectaSiteBundle:Page:navigation.html.twig', array('pages' => $pagesFiltered));
    }
}
