<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\UserBundle\Entity\User;
use Colecta\UserBundle\Entity\UserProfile;

class UserController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $users = $em->getRepository('ColectaUserBundle:User')->findBy(array(),array('name'=>'ASC'));
        
        return $this->render('ColectaBackendBundle:User:index.html.twig', array('users'=>$users));
    }
    public function rolesAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $roles = $em->createQuery('SELECT r, (SELECT COUNT(u) FROM ColectaUserBundle:User u WHERE u.role = r) amount FROM ColectaUserBundle:Role r ')->getResult();
        
        return $this->render('ColectaBackendBundle:User:roles.html.twig', array('roles'=>$roles));
    }
    public function profileAction($user_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $user = $em->getRepository('ColectaUserBundle:User')->findOneById($user_id);
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request')->request;
            $role = $em->getRepository('ColectaUserBundle:Role')->findOneById($request->get('userRole'));
            $profile = $user->getProfile();
            
            $user->setName($request->get('userName'));
            $user->setMail($request->get('userMail'));
            $user->setRole($role);
            
            $profile->setName($request->get('name'));
            $profile->setSurname($request->get('surname'));
            $profile->setSex($request->get('sex'));
            
            if($request->get('birthDateYear') && $request->get('birthDateMonth') && $request->get('birthDateDay'))
            {
                $year = intval($request->get('birthDateYear'));
                $month = intval($request->get('birthDateMonth'));
                $day = intval($request->get('birthDateDay'));
                
                if(1900 < $year  && $year <= intval(date('Y')) && 1 <= $month && $month <= 12 && 1 <= $day && $day <= 31)
                {
                    $birthDateTime = new \DateTime($year.'-'.$month.'-'.$day.' 00:00:00');
                    $profile->setBirthDate($birthDateTime);
                }
                else
                {
                    $this->get('session')->setFlash('error', 'La fecha de nacimiento '."".' no tiene un valor aceptable.');
                }
            }
            
            $profile->setAddress($request->get('address'));
            $profile->setPhone($request->get('phone'));
            $profile->setIdNumber($request->get('idNumber'));
            
            if($request->get('partnerId'))
            {
                $partnerIdUser = $em->getRepository('ColectaUserBundle:UserProfile')->findOneByPartnerId($request->get('partnerId'));
                
                if($partnerIdUser && $partnerIdUser->getUser() != $user)
                {
                    $this->get('session')->setFlash('error', 'El número de socio no se ha establecido porque pertenece a '.$partnerIdUser->getUser()->getName());
                }
                else
                {
                    $profile->setPartnerId($request->get('partnerId'));
                }
            }
            else
            {
                $profile->setPartnerId(null);
            }
            
            $profile->setComments($request->get('comments'));
            
            $em->persist($user); 
            $em->persist($profile); 
            $em->flush();
            $this->get('session')->setFlash('success', 'Modificado correctamente');
        }
        
        $roles = $em->getRepository('ColectaUserBundle:Role')->findAll();
        
        return $this->render('ColectaBackendBundle:User:profile.html.twig', array('user'=>$user, 'roles'=>$roles));
    }
    public function newUserAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $user = new User();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request')->request;
            
            if($request->get('userName'))
            {
                $user->setName($request->get('userName'));
            }
            else
            {
                $this->get('session')->setFlash('error', 'Debes escribir un nombre de usuario.');
            }
            
            if($request->get('userMail'))
            {
                $user->setMail($request->get('userMail'));
            }
            else
            {
                $this->get('session')->setFlash('error', 'Debes escribir un correo electrónico.');
            }
            
            $role = $em->getRepository('ColectaUserBundle:Role')->findOneById($request->get('userRole'));
            $user->setRole($role);
            
            $profile = new UserProfile();
            $user->setProfile($profile);
            
            $profile->setName($request->get('name'));
            $profile->setSurname($request->get('surname'));
            $profile->setSex($request->get('sex'));
            
            if($request->get('birthDateYear') && $request->get('birthDateMonth') && $request->get('birthDateDay'))
            {
                $year = intval($request->get('birthDateYear'));
                $month = intval($request->get('birthDateMonth'));
                $day = intval($request->get('birthDateDay'));
                
                if(1900 < $year  && $year <= intval(date('Y')) && 1 <= $month && $month <= 12 && 1 <= $day && $day <= 31)
                {
                    $birthDateTime = new \DateTime($year.'-'.$month.'-'.$day.' 00:00:00');
                    $profile->setBirthDate($birthDateTime);
                }
                else
                {
                    $this->get('session')->setFlash('error', 'La fecha de nacimiento '."".' no tiene un valor aceptable.');
                }
            }
            
            $profile->setAddress($request->get('address'));
            $profile->setPhone($request->get('phone'));
            $profile->setIdNumber($request->get('idNumber'));
            
            if($request->get('partnerId'))
            {
                $partnerIdUser = $em->getRepository('ColectaUserBundle:UserProfile')->findOneByPartnerId($request->get('partnerId'));
                
                if($partnerIdUser && $partnerIdUser->getUser() != $user)
                {
                    $this->get('session')->setFlash('error', 'El número de socio no se ha establecido porque pertenece a '.$partnerIdUser->getUser()->getName());
                }
                else
                {
                    $profile->setPartnerId($request->get('partnerId'));
                }
            }
            else
            {
                $profile->setPartnerId(null);
            }
            
            $profile->setComments($request->get('comments'));
            
            //Welcome Text
            $welcomeText = $request->get('welcomeText');
            
            if($user->getName() && $user->getMail())
            {
                $user->setPass('');
                $user->setAvatar('');
                $user->setRegistered(new \DateTime('now'));
                $user->setLastAccess(new \DateTime('now'));
                
                $salt = md5(time());
                $user->setSalt($salt);
                
                $code = substr(md5($salt.$this->container->getParameter('secret')),5,18);
                
                $em->persist($user);
                $em->persist($profile); 
                $em->flush();
                $this->get('session')->setFlash('success', 'Usuario '. $user->getName() .' creado correctamente');
                
                if($request->get('notificateUser') == 'on')
                {
                    $mailer = $this->get('mailer');
                    
                    //Get the mail address the message is sent from
                    $configmail = $this->container->getParameter('mail');
                    
                    //Get this web title
                    $twig = $this->container->get('twig');
                    $globals = $twig->getGlobals();
                    $webTitle = $globals['web_title'];
                    
                    //Welcome Text
                    $welcomeText = str_replace(array('%N', '%L'), array($user->getName(), $this->get('router')->generate('userResetPasswordCode', array('uid'=>$user->getId(), 'code'=>$code), true)), $welcomeText);
                    
                    $message = \Swift_Message::newInstance();
                    //$logo = $message->embed(\Swift_Image::fromPath($this->get('kernel')->getRootDir().'/../web/logo.png'));
    			    $message->setSubject('Cuenta creada en '.$webTitle)
    			        ->setFrom($configmail['from'])
    			        ->setTo($user->getMail())
    			        //->addPart($this->renderView('::foo.txt.twig', array(), 'text/plain')
    			        ->setBody($welcomeText);
    			    $mailer->send($message);
                }
                
                $this->get('request')->SetMethod("GET"); // Set request method to Get before redirection 
                return new RedirectResponse($this->generateUrl('ColectaBackendUserProfile', array('user_id'=>$user->getId()))); //User profile edit page
            }
        }
        else
        {
            //Get this web title
            $twig = $this->container->get('twig');
            $globals = $twig->getGlobals();
            $webTitle = $globals['web_title']; 
                    
            $welcomeText = str_replace('---web_title---', $webTitle, $this->container->getParameter('welcomeText'));
        }
        
        $roles = $em->getRepository('ColectaUserBundle:Role')->findAll();
        
        return $this->render('ColectaBackendBundle:User:newUser.html.twig', array('user'=>$user, 'roles'=>$roles, 'welcomeText'=>$welcomeText));
    }
    public function roleEditAction($role_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $role = $em->getRepository('ColectaUserBundle:Role')->findOneById($role_id);
        
        if($role->getName() != 'ROLE_CUSTOM') //Only custom roles can be edited
        {
            $this->get('request')->SetMethod("GET"); // Set request method to Get before redirection 
            return new RedirectResponse($this->generateUrl('ColectaBackendRolesIndex'));
        }
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request')->request;
            
            $role->set(checkbox($request->get('')));
            
            if($request->get('description') != "")
            {
                $em->persist($role); 
                $em->flush();
                $this->get('session')->setFlash('success', 'Modificado correctamente');
            }
            else
            {
                $this->get('session')->setFlash('error', 'Debes indicar un nombre al perfil');
            }
        }
        
        return $this->render('ColectaBackendBundle:User:roleEdit.html.twig', array('role'=>$role));
    }
    public function activityReportAction($format) //Special report for Turyciclo
    {
        $year = 2013;
        
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $year = min( intval(date('Y')), max( 1990, intval($year) ) );
        
        $users = $em->getRepository('ColectaUserBundle:User')->findAll();
        
        $kms = array(1=>'',2=>'');
        $jefesruta = array();
        
        if(count($users))
        {
            $newusers = array(); //the new array of users, that will be sorted by total kms
            foreach($users as $u)
            {
                if($u->getRole() && $u->getRole()->getName() != 'ROLE_BANNED')
                {
                    $k = 0;
                
                    $assistances = $em->createQuery('SELECT a FROM ColectaActivityBundle:Event e, ColectaActivityBundle:EventAssistance a WHERE a.event = e AND a.user = :user AND a.confirmed = 1 AND e.dateend >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$year.'-12-31 23:59:59\' ORDER BY e.dateini ASC')->setParameter('user', $u)->getResult();
                    
                    if(count($assistances))
                    {
                        foreach($assistances as $as)
                        {
                            $k += $as->getKm();
                        }
                    }
                    
                    $kms[$u->getId()] = $k;
                    $newusers[$k][] = $u;
                    
                    /// JEFES RUTA
                    $eventsUploadedConfirmed = $em->createQuery('SELECT e FROM ColectaActivityBundle:Event e, ColectaItemBundle:Item i, ColectaActivityBundle:EventAssistance a WHERE a.event = e AND e.id = i.id AND i.author = :user AND a.confirmed = 1 AND e.dateend >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$year.'-12-31 23:59:59\' ORDER BY e.dateini ASC')->setParameter('user', $u)->getResult();
                    
                    $jefesruta[$u->getId()] = $eventsUploadedConfirmed;
                }
            }
            
            // now we resort the array to make it of 1d instead of 2d
            $newusers2 = array();
            krsort($newusers);
            foreach($newusers as $nu)
            {
                foreach($nu as $nu2)
                {
                    $newusers2[] = $nu2;
                }
            }
            $users = $newusers2;
        }
        
        if($format == 'csv')
        {
            return $this->render('ColectaBackendBundle:User:activityReport.csv.twig', array('users'=>$users, 'kms'=>$kms, 'year'=>$year, 'jefesruta' => $jefesruta));

        }
        else
        {
            return $this->render('ColectaBackendBundle:User:activityReport.html.twig', array('users'=>$users, 'kms'=>$kms, 'year'=>$year, 'jefesruta' => $jefesruta));
        }
        
    }
}

function checkbox($value) //convert the value of a checkbox (on/off) to a boolean value (true/false)
{
    return $value == 'on' ? true : false;
}