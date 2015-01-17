<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\UserBundle\Entity\User;
use Colecta\UserBundle\Entity\UserProfile;
use Colecta\UserBundle\Entity\Role;

class UserController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
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
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $roles = $em->createQuery('SELECT r, (SELECT COUNT(u) FROM ColectaUserBundle:User u WHERE u.role = r) amount FROM ColectaUserBundle:Role r ')->getResult();
        
        return $this->render('ColectaBackendBundle:User:roles.html.twig', array('roles'=>$roles));
    }
    public function roleCreateAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $role = new Role();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request')->request;
            
            $role = $this->fillRoleForm($role, $request);
            
            if(strlen($role->getDescription()))
            {
                $em->persist($role); 
				$em->flush();
				$this->get('session')->getFlashBag()->add('success', 'Rol creado correctamente');
				
				return new RedirectResponse($this->generateUrl('ColectaBackendRolesIndex'));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'Debes escribir un nombre para el Rol');
            }
        }
        
        return $this->render('ColectaBackendBundle:User:roleCreate.html.twig', array('role'=>$role));
    }
    public function roleEditAction($role_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $role = $em->getRepository('ColectaUserBundle:Role')->findOneById($role_id);
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
	       	if($role->getName() != 'ROLE_CUSTOM') //Only custom roles can be edited
	        {
		        $this->get('session')->getFlashBag()->add('error', 'No puedes modificar este Rol');
	            return new RedirectResponse($this->generateUrl('ColectaBackendRolesIndex'));
	        }
	        
            $request = $this->get('request')->request;
            
            $role = $this->fillRoleForm($role, $request);
            
            if(strlen($request->get('description')))
            {
                $em->persist($role); 
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Rol modificado correctamente');
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'Debes indicar un nombre al perfil');
            }
        }
        
        return $this->render('ColectaBackendBundle:User:roleCreate.html.twig', array('role'=>$role));
    }
    public function roleDeleteAction($role_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $role = $em->getRepository('ColectaUserBundle:Role')->findOneById($role_id);
        
       	if($role->getName() != 'ROLE_CUSTOM') //Only custom roles can be edited
        {
	        $this->get('session')->getFlashBag()->add('error', 'No puedes modificar este Rol');
            return new RedirectResponse($this->generateUrl('ColectaBackendRolesIndex'));
        }
        
        $amount= $em->createQuery('SELECT COUNT(u) amount FROM ColectaUserBundle:User u WHERE u.role = :role')->setParameter('role', $role->getId())->getOneOrNullResult();
        
        if($amount == null or $amount['amount'] == 0)
        {
            $em->remove($role); 
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Rol eliminado correctamente');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', 'No puedes eliminar un Rol que esté en uso.');
        }
        
        return new RedirectResponse($this->generateUrl('ColectaBackendRolesIndex'));
    }
    public function profileAction($user_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
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
                    $this->get('session')->getFlashBag()->add('error', 'La fecha de nacimiento '."".' no tiene un valor aceptable.');
                }
            }
            
            $profile->setAddress($request->get('address'));
            $profile->setPhone($request->get('phone'));
            $profile->setIdNumber($request->get('idNumber'));
            
            if($request->get('partnerId'))
            {
                // Check format
                if(!is_numeric($request->get('partnerId')))
                {
                    $this->get('session')->getFlashBag()->add('error', 'El número de socio no se ha establecido porque tiene que ser numérico.');
                }
                else
                {
                    // Check for duplicity
                    $partnerIdUser = $em->getRepository('ColectaUserBundle:UserProfile')->findOneByPartnerId($request->get('partnerId'));
                
                    if($partnerIdUser && $partnerIdUser->getUser() != $user)
                    {
                        $this->get('session')->getFlashBag()->add('error', 'El número de socio no se ha establecido porque pertenece a '.$partnerIdUser->getUser()->getName());
                    }
                    else
                    {
                        $profile->setPartnerId($request->get('partnerId'));
                    }
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
            $this->get('session')->getFlashBag()->add('success', 'Modificado correctamente');
        }
        
        $roles = $em->getRepository('ColectaUserBundle:Role')->findAll();
        
        return $this->render('ColectaBackendBundle:User:profile.html.twig', array('user'=>$user, 'roles'=>$roles));
    }
    public function newUserAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigUsers())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        /* Check if new users can be created */
        
        $users = $em->getRepository('ColectaUserBundle:User')->findAll();
        
        $activeUsers = 0; //counter for active users, as inactive won't count for user creation limit
        
        foreach($users as $u) 
        {
            if($u->getRole()->getContribute())
            {
                $activeUsers++;
            }
        }
        
        $limitUsers = $this->container->getParameter('limit_users');
        
        if($activeUsers >= $limitUsers)
        {
            $this->get('session')->getFlashBag()->add('error', 'Has superado el límite de usuarios que puedes crear ('.$limitUsers.').');
            
            return $this->indexAction();
        }
        
        
        // New user handler
        $newUser = new User();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request')->request;
            
            if($request->get('userName'))
            {
                $newUser->setName($request->get('userName'));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'Debes escribir un nombre de usuario.');
            }
            
            if($request->get('userMail'))
            {
                $newUser->setMail($request->get('userMail'));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'Debes escribir un correo electrónico.');
            }
            
            $role = $em->getRepository('ColectaUserBundle:Role')->findOneById($request->get('userRole'));
            $newUser->setRole($role);
            
            $profile = new UserProfile();
            $newUser->setProfile($profile);
            
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
                    $this->get('session')->getFlashBag()->add('error', 'La fecha de nacimiento '."".' no tiene un valor aceptable.');
                }
            }
            
            $profile->setAddress($request->get('address'));
            $profile->setPhone($request->get('phone'));
            $profile->setIdNumber($request->get('idNumber'));
            
            if($request->get('partnerId'))
            {
                $partnerIdUser = $em->getRepository('ColectaUserBundle:UserProfile')->findOneByPartnerId($request->get('partnerId'));
                
                if($partnerIdUser && $partnerIdUser->getUser() != $newUser)
                {
                    $this->get('session')->getFlashBag()->add('error', 'El número de socio no se ha establecido porque pertenece a '.$partnerIdUser->getUser()->getName());
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
            
            if($newUser->getName() && $newUser->getMail())
            {
                $newUser->setPass('');
                $newUser->setAvatar('');
                $newUser->setRegistered(new \DateTime('now'));
                $newUser->setLastAccess(new \DateTime('now'));
                
                $salt = md5(time());
                $newUser->setSalt($salt);
                
                $code = substr(md5($salt.$this->container->getParameter('secret')),5,18);
                
                $em->persist($newUser);
                $em->persist($profile); 
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Usuario '. $newUser->getName() .' creado correctamente');
                
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
                    $welcomeText = str_replace(array('%N', '%L'), array($newUser->getName(), $this->get('router')->generate('userResetPasswordCode', array('uid'=>$newUser->getId(), 'code'=>$code), true)), $welcomeText);
                    
                    $message = \Swift_Message::newInstance();
                    //$logo = $message->embed(\Swift_Image::fromPath($this->get('kernel')->getRootDir().'/../web/logo.png'));
    			    $message->setSubject('Cuenta creada en '.$webTitle)
    			        ->setFrom($configmail['from'])
    			        ->setTo($newUser->getMail())
    			        //->addPart($this->renderView('::foo.txt.twig', array(), 'text/plain')
    			        ->setBody($welcomeText);
    			    $mailer->send($message);
                }
                
                $this->get('request')->SetMethod("GET"); // Set request method to Get before redirection 
                return new RedirectResponse($this->generateUrl('ColectaBackendUserProfile', array('user_id'=>$newUser->getId()))); //User profile edit page
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
        
        return $this->render('ColectaBackendBundle:User:newUser.html.twig', array('user'=>$newUser, 'roles'=>$roles, 'welcomeText'=>$welcomeText));
    }
    public function activityReportAction($format) //Special report for Turyciclo
    {
        $year = 2013;
        
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
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
    
    /* Custom function to fill Role object with request form object */
    public function fillRoleForm($role, $request)
    {
	    /* DESCRIPTION */
	    $role->setDescription($request->get('description'));
	    
	    /* GLOBAL ACCESS */
	    $role->setSiteAccess(checkbox($request->get('site_access')));
	    
	    /* VIEW ACTIONS */
	    $role->setItemPostView(checkbox($request->get('item_post_view')));
	    $role->setItemEventView(checkbox($request->get('item_event_view')));
	    $role->setItemRouteView(checkbox($request->get('item_route_view')));
	    $role->setItemPlaceView(checkbox($request->get('item_place_view')));
	    $role->setItemFileView(checkbox($request->get('item_file_view')));
	    $role->setItemContestView(checkbox($request->get('item_contest_view')));
	    $role->setItemPollView(checkbox($request->get('item_poll_view')));
	    
	    /* CREATE ACTIONS */
	    $role->setItemPostCreate(checkbox($request->get('item_post_create')));
	    $role->setItemEventCreate(checkbox($request->get('item_event_create')));
	    $role->setItemRouteCreate(checkbox($request->get('item_route_create')));
	    $role->setItemPlaceCreate(checkbox($request->get('item_place_create')));
	    $role->setItemFileCreate(checkbox($request->get('item_file_create')));
	    $role->setItemContestCreate(checkbox($request->get('item_contest_create')));
	    $role->setItemPollCreate(checkbox($request->get('item_poll_create')));
	    
	    /* EDIT ACTIONS */
	    $role->setItemPostEdit(checkbox($request->get('item_post_edit')));
	    $role->setItemEventEdit(checkbox($request->get('item_event_edit')));
	    $role->setItemRouteEdit(checkbox($request->get('item_route_edit')));
	    $role->setItemPlaceEdit(checkbox($request->get('item_place_edit')));
	    $role->setItemFileEdit(checkbox($request->get('item_file_edit')));
	    $role->setItemContestEdit(checkbox($request->get('item_contest_edit')));
	    $role->setItemPollEdit(checkbox($request->get('item_poll_edit')));
	    
	    /* EDIT_ANY ACTIONS */
	    $role->setItemPostEditAny(checkbox($request->get('item_post_edit_any')));
	    $role->setItemEventEditAny(checkbox($request->get('item_event_edit_any')));
	    $role->setItemRouteEditAny(checkbox($request->get('item_route_edit_any')));
	    $role->setItemPlaceEditAny(checkbox($request->get('item_place_edit_any')));
	    $role->setItemFileEditAny(checkbox($request->get('item_file_edit_any')));
	    $role->setItemContestEditAny(checkbox($request->get('item_contest_edit_any')));
	    $role->setItemPollEditAny(checkbox($request->get('item_poll_edit_any')));
	    
	    /* RELATE */
	    $role->setItemRelateOwn(checkbox($request->get('item_relate_own')));
	    $role->setItemRelateAny(checkbox($request->get('item_relate_any')));
	    
	    /* COMMENT ACTIONS */
	    $role->setItemPostComment(checkbox($request->get('item_post_comment')));
	    $role->setItemEventComment(checkbox($request->get('item_event_comment')));
	    $role->setItemRouteComment(checkbox($request->get('item_route_comment')));
	    $role->setItemPlaceComment(checkbox($request->get('item_place_comment')));
	    $role->setItemFileComment(checkbox($request->get('item_file_comment')));
	    $role->setItemContestComment(checkbox($request->get('item_contest_comment')));
	    $role->setItemPollComment(checkbox($request->get('item_poll_comment')));
	    
	    /* POLL VOTE */
	    
	    $role->setItemPollVote(checkbox($request->get('item_poll_vote')));
	    
	    /* CATEGORY */
	    $role->setCategoryCreate(checkbox($request->get('category_create')));
	    $role->setCategoryEdit(checkbox($request->get('category_edit')));
	    
	    /* ACTIVITY */
	    $role->setActivityCreate(checkbox($request->get('activity_create')));
	    $role->setActivityEdit(checkbox($request->get('activity_edit')));
	    
	    /* USERS */
	    $role->setUserView(checkbox($request->get('user_view')));
	    $role->setMessageSend(checkbox($request->get('message_send')));
	    $role->setUserCreate(checkbox($request->get('user_create')));
	    $role->setUserEdit(checkbox($request->get('user_edit')));
	    
	    /* SITE CONFIG */
	    $role->setSiteConfigSettings(checkbox($request->get('site_config_settings')));
	    $role->setSiteConfigUsers(checkbox($request->get('site_config_users')));
	    $role->setSiteConfigPages(checkbox($request->get('site_config_pages')));
	    $role->setSiteConfigLottery(checkbox($request->get('site_config_lottery')));
	    $role->setSiteConfigPlan(checkbox($request->get('site_config_plan')));
	    $role->setSiteConfigStats(checkbox($request->get('site_config_stats')));
	    
	    return $role;
    }
}

function checkbox($value) //convert the value of a checkbox (on/off) to a boolean value (true/false)
{
    return $value == 'on' ? true : false;
}