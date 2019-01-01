<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\Query\ResultSetMapping;

class ActivitiesController extends Controller
{
    
    public function indexAction()
    {
        return $this->yearPerformanceAction(date('Y'));
    }
    
    public function yearPerformanceAction($year)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        if(!$user)
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $year = min( intval(date('Y')), max( 1960, intval($year) ) );
        
        $assistances = $em->createQuery('SELECT a FROM ColectaActivityBundle:Event e, ColectaActivityBundle:EventAssistance a WHERE a.event = e AND a.user = :user AND a.confirmed = 1 AND e.dateend >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$year.'-12-31 23:59:59\' ORDER BY e.dateini ASC')->setParameter('user',$user)->getResult();
        
        $pointsRequest = $em->createQuery('SELECT p FROM ColectaUserBundle:Points p, ColectaActivityBundle:Event e WHERE p.item = e AND p.user = :user AND  e.dateini >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$year.'-12-31 23:59:59\'')->setParameter('user',$user)->getResult();
        
        $points = array();
        foreach($pointsRequest as $p)
        {
            $points[$p->getItem()->getId()] = $p;
        }
        
        $stmt = $em  
               ->getConnection()  
               ->prepare('SELECT DISTINCT(YEAR(e.dateini)) AS year FROM Event e INNER JOIN EventAssistance a ON e.id = a.event_id WHERE e.id = a.event_id AND a.user_id = :user_id AND e.dateend <= \''.date('Y').'-12-31 00:00:00\' ORDER BY e.dateini ASC');
               
        $stmt->bindValue('user_id', $user->getId());  
        $stmt->execute();  
        $years = $stmt->fetchAll();
        
        return $this->render('ColectaIntranetBundle:Activities:index.html.twig', array('assistances'=>$assistances, 'points'=>$points, 'year'=>$year, 'years'=>$years));
    }
    
    public function rankAction()
    {
        return $this->yearPointsRankAction(date('Y'));
    }
    
    public function yearKmRankAction($year)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $year = min( intval(date('Y')), max( 1960, intval($year) ) );
        
        $users = $em->getRepository('ColectaUserBundle:User')->findAll();
        
        $kms = array(1=>'',2=>'');
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
        
        $stmt = $em  
               ->getConnection()  
               ->prepare('SELECT DISTINCT(YEAR(e.dateini)) AS year FROM Event e WHERE  e.dateend <= \''.date('Y').'-12-31 00:00:00\' ORDER BY YEAR(e.dateini) ASC');
               
        $stmt->execute();  
        $years = $stmt->fetchAll();
        
        return $this->render('ColectaIntranetBundle:Activities:rank.html.twig', array('users'=>$users, 'kms'=>$kms, 'year'=>$year, 'years'=>$years));
    }
    public function yearPointsRankAction($year)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $year = min( intval(date('Y')), max( 2000, intval($year) ) );
        
        $users = $em->getRepository('ColectaUserBundle:User')->findBy(array(),array('name'=>'ASC'));
        
        $points = array(1=>'',2=>'');
        $kms = array(1=>'',2=>'');
        
        if(count($users))
        {
            $newusers = array(); //the new array of users, that will be sorted by total points
            foreach($users as $u)
            {
                if($u->getRole() && $u->getRole()->getName() != 'ROLE_BANNED')
                {
                    $p = 0;
                    
                    $eventsDateEnd = $year.'-12-31 23:59:59';
                    if($year == date('Y')){
	                    $eventsDateEnd = date('Y-m-d H:i:s');
                    }
                
                    $pointsRequest = $em->createQuery('SELECT p FROM ColectaUserBundle:Points p, ColectaActivityBundle:Event e WHERE p.item = e AND p.user = :user AND  e.dateini >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$eventsDateEnd.'\'')->setParameter('user',$u)->getResult();
                    
                    if(count($pointsRequest))
                    {
                        foreach($pointsRequest as $point)
                        {
                            $p += $point->getPoints();
                        }
                    }
                    
                    $points[$u->getId()] = $p;
                    
                    //now count kms
                    
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
                    
                    $newusers[$p][] = $u;
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
        
        //Get the years with assistances
        $stmt = $em  
               ->getConnection()  
               ->prepare('SELECT DISTINCT(YEAR(e.dateini)) AS year FROM Event e WHERE  e.dateend <= \''.date('Y').'-12-31 00:00:00\' ORDER BY YEAR(e.dateini) ASC');
               
        $stmt->execute();  
        $years = $stmt->fetchAll();
        
        return $this->render('ColectaIntranetBundle:Activities:pointsrank.html.twig', array('users'=>$users, 'points'=>$points, 'kms'=>$kms, 'year'=>$year, 'years'=>$years));
    }
    
    public function RankSummaryAction()
    {
	    $year = date('Y');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        if(!$user)
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $year = min( intval(date('Y')), max( 1960, intval($year) ) );
        
        $users = $em->getRepository('ColectaUserBundle:User')->findBy(array(),array('name'=>'ASC'));
        
        $points = array(1=>'',2=>'');
        $kms = array(1=>'',2=>'');
        
        if(count($users))
        {
            $newusers = array(); //the new array of users, that will be sorted
            foreach($users as $u)
            {
                if($u->getRole() && $u->getRole()->getName() != 'ROLE_BANNED')
                {
                    $p = 0;
                    
                    $eventsDateEnd = $year.'-12-31 23:59:59';
                    if($year == date('Y')){
	                    $eventsDateEnd = date('Y-m-d H:i:s');
                    }
                
                    $pointsRequest = $em->createQuery('SELECT p FROM ColectaUserBundle:Points p, ColectaActivityBundle:Event e WHERE p.item = e AND p.user = :user AND  e.dateini >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$eventsDateEnd.'\'')->setParameter('user',$u)->getResult();
                    
                    if(count($pointsRequest))
                    {
                        foreach($pointsRequest as $point)
                        {
                            $p += $point->getPoints();
                        }
                    }
                    
                    $points[$u->getId()] = $p;
                    
                    $newusers[$p][] = $u;
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
            
            $userpos = 0;
            foreach($users as $k=>$v)
            {
	            if($v == $user)
	            {
		            $userpos = $k;
		            break;
	            }
            }
        }
        
        return $this->render('ColectaIntranetBundle:Activities:pointsranksummary.html.twig', array('users'=>$users, 'points'=>$points, 'userpos' => $userpos));
    }
    
    public function performanceSummaryAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        if(!$user)
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $assistances = $em->createQuery('SELECT a FROM ColectaActivityBundle:Event e, ColectaActivityBundle:EventAssistance a WHERE a.event = e AND a.user = :user AND a.confirmed = 1 AND e.dateini >= DATE_SUB(CURRENT_DATE(),30, \'day\')')->setParameter('user',$user)->getResult();
        
        $prev_assistances = $em->createQuery('SELECT a FROM ColectaActivityBundle:Event e, ColectaActivityBundle:EventAssistance a WHERE a.event = e AND a.user = :user AND a.confirmed = 1 AND e.dateini >= DATE_SUB(CURRENT_DATE(),60, \'day\') AND e.dateend <= DATE_SUB(CURRENT_DATE(),30, \'day\')')->setParameter('user',$user)->getResult();
        
        $pointsRequest = $em->createQuery('SELECT p FROM ColectaUserBundle:Points p, ColectaActivityBundle:Event e WHERE p.item = e AND p.user = :user AND  e.dateini >= DATE_SUB(CURRENT_DATE(),60, \'day\') ')->setParameter('user',$user)->getResult();
        
        $points = array();
        foreach($pointsRequest as $p)
        {
            $points[$p->getItem()->getId()] = $p;
        }
        
        $pointsSum = $kmsSum = 0;
        $daysAgoKms = array_fill(0, 30, 0); //initialize array of kms where key represents how many days ago
        $today = new \DateTime();
        
        foreach($assistances as $assistance)
        {
	        $kmsSum += $assistance->getKm();
	        $pointsSum += $points[$assistance->getEvent()->getId()]->getPoints();
	        
	        $daysAgo = $assistance->getEvent()->getDateini()->diff($today)->d;
	        $daysAgoKms[$daysAgo] += $assistance->getKm();
        }
        
        $prev_pointsSum = $prev_kmsSum = 0;
        
        foreach($prev_assistances as $assistance)
        {
	        $prev_kmsSum += $assistance->getKm();
	        $prev_pointsSum += $points[$assistance->getEvent()->getId()]->getPoints();
        }
        
        return $this->render('ColectaIntranetBundle:Activities:performancesummary.html.twig', array('kmsSum'=>$kmsSum, 'pointsSum'=>$pointsSum, 'prev_kmsSum'=>$prev_kmsSum, 'prev_pointsSum'=>$prev_pointsSum, 'daysAgoKms'=>$daysAgoKms));
    }
    
    public function yearTuryRankAction($year)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $year = min( intval(date('Y')), max( 1960, intval($year) ) );
        
        $users = $em->getRepository('ColectaUserBundle:User')->findBy(array(),array('name'=>'ASC'));
        
        $points = array(1=>'',2=>'');
        $kms = array(1=>'',2=>'');
        
        if(count($users))
        {
            $newusers = array(); //the new array of users, that will be sorted by total kms
            foreach($users as $u)
            {
                if($u->getRole() && $u->getRole()->getName() != 'ROLE_BANNED')
                {
                    $p = 0;
                
                    $pointsRequest = $em->createQuery('SELECT p FROM ColectaUserBundle:Points p, ColectaActivityBundle:Event e WHERE p.item = e AND p.user = :user AND  e.dateini >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$year.'-12-31 23:59:59\'')->setParameter('user',$u)->getResult();
                    
                    if(count($pointsRequest))
                    {
                        foreach($pointsRequest as $point)
                        {
                            $p += $point->getPoints();
                        }
                    }
                    
                    $points[$u->getId()] = $p;
                    
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
                    
                    $newusers[$p][] = $u;
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
        
        //Get the years with assistances
        $stmt = $em  
               ->getConnection()  
               ->prepare('SELECT DISTINCT(YEAR(e.dateini)) AS year FROM Event e WHERE  e.dateend <= \''.date('Y').'-12-31 00:00:00\' ORDER BY e.dateini ASC');
               
        $stmt->execute();  
        $years = $stmt->fetchAll();
        
        $response = $this->render('ColectaIntranetBundle:Activities:turyrank.html.twig', array('users'=>$users, 'points'=>$points, 'kms'=>$kms, 'year'=>$year, 'years'=>$years)); 
    
        $response->setStatusCode(200);
        $response->setCharset('UTF-8');
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Description', 'Submissions Export');
        $response->headers->set('Content-Disposition', 'attachment; filename=export_'.$year.'.csv');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response; 
    }
}
