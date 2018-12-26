<?php

namespace Colecta\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Colecta\SiteBundle\Entity\ContactRequest;

class PageController extends Controller
{
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        
        $page = $em->getRepository('ColectaSiteBundle:Page')->findOneBySlug($slug);
        
        if(! $page)
        {
             throw $this->createNotFoundException('No existe la página que buscas');
        }
        
        $user = $this->getUser();
        
        /* Get the user role id, if annonymous role id = 0 */
        if(!$user)
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
            
            if($this->get('request')->getMethod() == 'POST' and $page->getContact())
            {
                $request = $this->get('request')->request;
                
                if($request->get('phone') == '' && $this->idIsValid($request->get('valid_id'))) // anti-spam systems
                {      
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
	                
	                $sendTo = isset($contactData['email']) && !empty($contactData['email']) ? $contactData['email'] : $configmail['admin'];
	                
	                /* Send mail to admin */
	                $mailer = $this->get('mailer');
	                $configmail = $this->container->getParameter('mail');
	                
	                $message = \Swift_Message::newInstance();
	    		    $message->setSubject('Formulario ['. $page->getName() .']')
	    		        ->setFrom($configmail['from'])
	    		        //->setReplyTo(array($email => $name))
	    		        ->setTo($sendTo)
	    		        ->setBody($this->renderView('ColectaSiteBundle:Page:contactRequest.txt.twig', array('page' => $page, 'contactRequest'=>$contactRequest)), 'text/plain');
	    		    $mailer->send($message);
	    		    
	    		    $this->get('session')->getFlashBag()->add('success', 'El formulario se ha enviado correctamente.');
	                $this->get('session')->getFlashBag()->add('pageFormSent','1');
    		    }
            }
            
            $categories = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE (c.posts + c.routes + c.events + c.files + c.places) > 0 ORDER BY c.name ASC'
			)->setFirstResult(0)->setMaxResults(50)->getResult();
            
            $valid_id = base64_encode(time()); // Anti-spam system. I will check if the form takes too few seconds to fill
            
            return $this->render('ColectaSiteBundle:Page:view.html.twig', array('page' => $page, 'contactRequest' => $contactRequest, 'categories'=>$categories, 'valid_id' => $valid_id));
        }
        else
        {
            throw $this->createNotFoundException('No tienes permiso para visualizar la página.');
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
    
	protected function idIsValid($value)
	{
		$time = base64_decode($value);
		
		if (!$time)
		{
			return false;
		}
		
		if (!is_numeric($time))
		{
			return false;
		}
		
		$time_ago = time() - $time;
		
		if ($time_ago > 84600)
		{
			return false;
		}
		
		if ($time_ago < 8)
		{
			return false;
		}
		
		return true;
	}
    
    public function navigationAction()
    {
        $em = $this->getDoctrine()->getManager();
        if($this->get('security.context')->getToken())
        {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        else
        {
            $user = 'anon.';
        }
        
        //Get all the pages that are not draftsand are ment to be on the sidebar
        $pages = $em->getRepository('ColectaSiteBundle:Page')->findBy(array('draft'=>0, 'sidebarShow'=>1), array('sidebarOrder'=>'ASC'));
        
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
