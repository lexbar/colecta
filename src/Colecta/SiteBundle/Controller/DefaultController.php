<?php

namespace Colecta\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /*public function indexAction($name)
    {
        return $this->render('ColectaSiteBundle:Default:index.html.twig', array('name' => $name));
    }*/
    
    function contactAction()
    {
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request');
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $text = $request->request->get('text');
            
            if(empty($name) || empty($email) || empty($text))
            {
                $this->get('session')->getFlashBag()->add('ContactName', $name);
                $this->get('session')->getFlashBag()->add('ContactEmail', $email);
                $this->get('session')->getFlashBag()->add('ContactText', $text);
                
                $this->get('session')->getFlashBag()->add('error', 'No puedes dejar ningún campo vacío');
            }
            elseif(preg_match("#^[^@]+@[^\.]+\.[^ ]+$#",$email) == 0)
            {
                $this->get('session')->getFlashBag()->add('ContactName', $name);
                $this->get('session')->getFlashBag()->add('ContactEmail', $email);
                $this->get('session')->getFlashBag()->add('ContactText', $text);
                
                $this->get('session')->getFlashBag()->add('error', 'El email no parece estar bien escrito');
            }
            else
            {
                $mailer = $this->get('mailer');
                $configmail = $this->container->getParameter('mail');
                
                $message = \Swift_Message::newInstance();
    		    $message->setSubject('Contacto en Ciclubs')
    		        ->setFrom($configmail['from'])
    		        ->setReplyTo(array($email => $name))
    		        ->setTo($configmail['admin'])
    		        ->setBody($this->renderView('ColectaSiteBundle:Default:contactmail.txt.twig', array('name'=>$name, 'email'=>$email, 'text'=>$text)), 'text/plain');
    		    $mailer->send($message);
    		    
    		    $this->get('session')->getFlashBag()->add('success', 'Mensaje enviado correctamente');
            }
        }
        
        return $this->render('ColectaSiteBundle:Default:contact.html.twig');
    }
}
