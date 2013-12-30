<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
    public function activityReportAction($format)
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
