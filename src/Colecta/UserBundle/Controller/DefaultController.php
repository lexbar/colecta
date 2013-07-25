<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DefaultController extends Controller
{
    
    public function loginAction()
    {
        $request = $this->getRequest(); 
        $session = $request->getSession();
        
        $error = $request->attributes->get( 
            SecurityContext::AUTHENTICATION_ERROR, 
            $session->get(SecurityContext::AUTHENTICATION_ERROR)
        );
        
        return $this->render('ColectaUserBundle:Default:login.html.twig', array( 
            'last_username' => $session->get(SecurityContext::LAST_USERNAME), 
            'error'	=>$error)
        );
    }
    public function registerAction() 
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager(); 
        
        $user = new User();
        
        $form = $this->createFormBuilder($user)
            ->add('name',   'text',     array('label'=>'Nombre:',       'required'=>true))
            ->add('mail',   'email',    array('label'=>'Email:',        'required'=>true))
            //->add('pass',   'password', array('label'=>'Contraseña:',   'required'=>false))
            ->add('pass', 'repeated', array(
                                        'type' => 'password',
                                        'invalid_message' => 'Las contraseñas deben coincidir.',
                                        'required' => true,
                                        'first_options'  => array('label' => 'Nueva Contraseña'),
                                        'second_options' => array('label' => 'Repite Contraseña')))
            ->getForm()
        ;
        
        $error = '';
        
        if ($request->getMethod() == 'POST') 
        {
            $form->bindRequest($request);
            
            $code = $request->request->get('code');
            $error = '';
            
            $regexp = strtoupper(str_replace(array('0','o','5','s'),array('[O|0]','[O|0]','[S|5]','[S|5]'), (strtolower($code))));
            $sql = "SELECT * FROM `pendrives` WHERE `code` REGEXP '^$regexp$'";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if(count($result))
            {                
                if ($form->isValid()) 
                {
                    $encoder = $this->get('security.encoder_factory')
                                    ->getEncoder($user); 
                    $user->setSalt(md5(time()));
                    $encodedpass = $encoder->encodePassword( $user->getPassword(), $user->getSalt()); 
                    $user->setPass($encodedpass);
                    $user->setCreation(new \DateTime('now'));
                    $user->setLastaccess(new \DateTime('now'));
                    
                    $em->persist($user); 
                    $em->flush();
                    
                    $token = new UsernamePasswordToken($user, null, 'main', array('ROLE_USER'));
                    $this->get('security.context')->setToken($token);
                    
                    return $this->redirect($this->generateUrl('home'));
                }
                else
                {
                    $error = 'Ha habido problemas procesando el formulario.';
                }
            }
            else
            {
                $error = 'El código no es correcto.';
                $code = '';
            }
        }
        else
        {
            $code = $request->query->get('code');
        }
        
        return $this->render( 'UserBundle:Default:register.html.twig', array('form' => $form->createView(), 'code'=> $code, 'error'=>$error) );
    }
    public function resetPasswordAction()
    {
        $request = $this->getRequest();
        
        if($request->getMethod() == 'POST')
        {
            $em = $this->getDoctrine()->getEntityManager();
            
            $email = $request->request->get('email');
            $user = $em->getRepository('ColectaUserBundle:User')->findOneBy(array('mail'=>$email));
            
            if($user)
            {
                $salt = $user->getSalt();
                
                if(empty($salt))
                {
                    $salt = md5(time());
                    $user->setSalt($salt);
                    $em->persist($user); 
                    $em->flush();
                }
                
                $code = substr(md5($salt.$this->container->getParameter('secret')),5,18);
                
                
                $mailer = $this->get('mailer');
                $configmail = $this->container->getParameter('mail');
                
                $message = \Swift_Message::newInstance();
                //$logo = $message->embed(\Swift_Image::fromPath($this->get('kernel')->getRootDir().'/../web/logo.png'));
			    $message->setSubject('Regenerar contraseña - Ciclubs')
			        ->setFrom($configmail['from'])
			        ->setTo($user->getMail())
			        //->addPart($this->renderView('CaucesTiendaBundle:Mail:regalo.txt.twig', array('request'=>$r, 'payment'=>$payment)), 'text/plain')
			        ->setBody($this->renderView('ColectaUserBundle:Default:resetPasswordMail.txt.twig', array('user'=>$user, 'code'=>$code)), 'text/plain');
			    $mailer->send($message);
			    
			    $this->get('session')->setFlash('resetmailsuccess', 'Te hemos enviado un email. Revisa tu bandeja de entrada, y la de spam por si acaso.');
			    return $this->render('ColectaUserBundle:Default:resetPassword.html.twig');
            }
            else
            {
                $this->get('session')->setFlash('resetmailerror', 'El email no está en nuestra base de datos');
                $this->get('session')->setFlash('email', $email);
            }
        }
        return $this->render('ColectaUserBundle:Default:resetPassword.html.twig');
    }
    public function newPasswordAction($uid, $code)
    {        
        $em = $this->getDoctrine()->getEntityManager();
        
        $user = $em->getRepository('ColectaUserBundle:User')->find($uid);
        
        if($user)
        {
            $salt = $user->getSalt();
            $thecode = substr(md5($salt.$this->container->getParameter('secret')),5,18);
            
            if($thecode == $code)
            {
                $request = $this->getRequest();
        
                if($request->getMethod() == 'POST')
                {
                    if( $request->request->get('pass1') != $request->request->get('pass2') )
                    {
                        $this->get('session')->setFlash('error', 'Las contraseñas no coinciden.');
                    }
                    else
                    {
                        //New Salt
                        $user->setSalt(md5(time()));
                        
                        $encoder = $this->get('security.encoder_factory')
                                    ->getEncoder($user); 
                        $encodedpass = $encoder->encodePassword( $request->request->get('pass1'), $user->getSalt()); 
                        
                        $user->setPass($encodedpass);
                       
                        
                        $em->persist($user); 
                        $em->flush();
                        
                        $token = new UsernamePasswordToken($user, null, 'main', array('ROLE_USER'));
                        $this->get('security.context')->setToken($token);
                        
                        return $this->redirect($this->generateUrl('ColectaDashboard'));
                    }
                }
                
                return $this->render('ColectaUserBundle:Default:newPassword.html.twig', array('user'=>$user, 'code'=>$code));
            }
            else
            {
                $this->get('session')->setFlash('error', 'El código no es correcto.');
                return $this->render('ColectaUserBundle:Default:resetPassword.html.twig');
            }
        }
        else
        {
            $this->get('session')->setFlash('error', 'El usuario no está en nuestra base de datos.');
        }
            
        return $this->render('ColectaUserBundle:Default:resetPassword.html.twig');
    }
}
