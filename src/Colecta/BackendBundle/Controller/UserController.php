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
        
        return $this->render('ColectaBackendBundle:User:profile.html.twig', array('user'=>$user));
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
