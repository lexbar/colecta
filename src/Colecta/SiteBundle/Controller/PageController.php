<?php

namespace Colecta\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Colecta\SiteBundle\Entity\ContactRequest;

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
            $contactRequestUser = null;
        }
        else
        {
            $role = $user->getRole()->getId();
            $contactRequestUser = $user;
        }
        
        /* If the user role is allowed to access the page */
        if(in_array($role, $page->getTargetRoles()))
        {
            $contactRequest = new ContactRequest();
            
            if($this->get('request')->getMethod() == 'POST' and $page->getContact() )
            {
                $request = $this->get('request')->request;
                $contactRequest->setPage($page);
                $contactRequest->setUser($contactRequestUser);
                $contactRequest->setDate(new \DateTime('now'));
                
                $data = array();
                $contactData = $page->getContactData();
                foreach($contactData['fields'] as $key=>$field)
                {
                    $data[$key] = $this->validateFormType($field['type'], $request->get('field'.$key));
                }
                
                $contactRequest->setData($data);
                
                $em->persist($contactRequest); 
                $em->flush();
                
                $this->get('session')->setFlash('success', 'El formulario se ha enviado correctamente.');
                $this->get('session')->setFlash('pageFormSent','1');
                
                /* Send mail to admin */
                /*$mailer = $this->get('mailer');
                $configmail = $this->container->getParameter('mail');
                
                $message = \Swift_Message::newInstance();
    		    $message->setSubject('Formulario ['. $page->getName() .']')
    		        ->setFrom($configmail['from'])
    		        ->setReplyTo(array($email => $name))
    		        ->setTo($configmail['admin'])
    		        ->setBody($this->renderView('ColectaSiteBundle:Default:contactmail.txt.twig', array('name'=>'FIX', 'email'=>'FIX', 'text'=>'FIX')), 'text/plain');
    		    $mailer->send($message);*/
            }
            
            return $this->render('ColectaSiteBundle:Page:view.html.twig', array('page' => $page, 'contactRequest' => $contactRequest));
        }
        else
        {
            throw $this->createNotFoundException('No tienes permisos para visualizar la página.');
        }
    }
    
    public function validateFormType($type, $text)
    {
        switch($type)
        {
            case 'checkbox':
                return $text == 'on' ? true : false;
            break;
            default:
                return $text;
            break;
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
