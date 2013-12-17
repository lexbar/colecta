<?php

namespace Colecta\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Colecta\IntranetBundle\Entity\LotteryCampaign;
use Colecta\IntranetBundle\Entity\LotteryShred;

class LotteryController extends Controller
{
    
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if($user == 'anon.')
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $campaign = $em->createQuery('SELECT c FROM ColectaIntranetBundle:LotteryCampaign c ORDER BY c.date DESC')->getSingleResult();
        $shreds = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.lotteryCampaign = :campaign AND s.user = :user ORDER BY s.date ASC')->setParameters(array('campaign'=>$campaign, 'user'=>$user))->getResult();
        
        return $this->render('ColectaIntranetBundle:Lottery:index.html.twig', array('lotteryCampaign'=>$campaign, 'lotteryShreds'=>$shreds));
    }
}
