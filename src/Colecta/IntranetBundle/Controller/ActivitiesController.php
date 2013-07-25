<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;


class ActivitiesController extends Controller
{
    
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $asssistances = $em->createQuery('SELECT a FROM ColectaActivityBundle:Event e, ColectaActivityBundle:EventAssistance a WHERE a.event = e AND a.user = :user AND a.confirmed = 1 ORDER BY e.dateini ASC')->setParameter('user', $user)->getResult();
        
        return $this->render('ColectaIntranetBundle:Activities:index.html.twig', array('asssistances'=>$asssistances));
    }
    public function rankAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $users = $em->getRepository('ColectaUserBundle:User')->findAll();
        
        $kms = array(1=>'',2=>'');
        if(count($users))
        {
            $newusers = array(); //the new array of users, that will be sorted by total kms
            foreach($users as $u)
            {
                if($u->getRole()->getName() != 'ROLE_BANNED')
                {
                    $k = 0;
                
                    $assistances = $em->createQuery('SELECT a FROM ColectaActivityBundle:Event e, ColectaActivityBundle:EventAssistance a WHERE a.event = e AND a.user = :user AND a.confirmed = 1 ORDER BY e.dateini ASC')->setParameter('user', $u)->getResult();
                    
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
        
        return $this->render('ColectaIntranetBundle:Activities:rank.html.twig', array('users'=>$users, 'kms'=>$kms));
    }
}
