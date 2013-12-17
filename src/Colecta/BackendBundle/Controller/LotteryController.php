<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\IntranetBundle\Entity\LotteryCampaign;
use Colecta\IntranetBundle\Entity\LotteryShred;
class LotteryController extends Controller
{    
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $campaign = $em->createQuery('SELECT c FROM ColectaIntranetBundle:LotteryCampaign c ORDER BY c.date DESC')->getSingleResult();
        $shreds = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.lotteryCampaign = :campaign ORDER BY s.date ASC')->setParameters(array('campaign'=>$campaign))->getResult();
        
        $userShreds = array();
        foreach($shreds as $s)
        {
            $userShreds[$s->getUser()->getId()][] = $s;
        }
        
        return $this->render('ColectaBackendBundle:Lottery:index.html.twig', array('lotteryCampaign'=>$campaign, 'lotteryShreds'=>$userShreds));
    }
    
    public function userAction($user_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $campaign = $em->createQuery('SELECT c FROM ColectaIntranetBundle:LotteryCampaign c ORDER BY c.date DESC')->getSingleResult();
        $targetUser = $em->createQuery('SELECT u FROM ColectaUserBundle:User u WHERE u.id = :id')->setParameter('id',$user_id)->getSingleResult();
        $shreds = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.lotteryCampaign = :campaign AND s.user = :user ORDER BY s.date ASC')->setParameters(array('campaign'=>$campaign, 'user'=>$targetUser))->getResult();
        
        return $this->render('ColectaBackendBundle:Lottery:user.html.twig', array('lotteryCampaign'=>$campaign, 'lotteryShreds'=>$shreds, 'targetUser'=>$targetUser));
    }
    
    public function addShredAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->get('request')->request;
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $targetUser = $em->createQuery('SELECT u FROM ColectaUserBundle:User u WHERE u.name = :uname')->setParameter('uname',$request->get('user'))->getSingleResult();
        if(!$targetUser)
        {
            $this->get('session')->setFlash('error', 'No se ha encontrado al usuario.');
            
            $login = $this->generateUrl('ColectaBackendLotteryIndex');
            return new RedirectResponse($login);
        }
        
        $campaign = $em->createQuery('SELECT c FROM ColectaIntranetBundle:LotteryCampaign c ORDER BY c.date DESC')->getSingleResult();
        
        $shred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.lotteryCampaign = :campaign AND s.start <= :end AND s.end >= :start ORDER BY s.date ASC')->setParameters(array('campaign'=>$campaign, 'start'=>$request->get('start'), 'end'=>$request->get('end')))->getResult();
        
        if(count($shred))
        {
            if($this->checkRepeatedShred($em, $shred[0], $request->get('start'), $request->get('end')))
            {
                $this->get('session')->setFlash('error', 'La selección es incompatible');
                
                $login = $this->generateUrl('ColectaBackendLotteryIndex');
                return new RedirectResponse($login);
            }
        }
                
        $newShred = new LotteryShred();
        $newShred->setUser($targetUser);
        $newShred->setStart($request->get('start'));
        $newShred->setEnd($request->get('end'));
        $newShred->setDate(new \DateTime('now'));
        if($request->get('paid') == 'on')
        {
            $newShred->setPaid(1);
        }
        else
        {
            $newShred->setPaid(0);
        }
        $newShred->setReturned(0);
        $newShred->setLotteryCampaign($campaign);
        
        $em->persist($newShred); 
        $em->flush();
        
        $this->get('session')->setFlash('success', 'Entrega de papeletas registrada.');
        
        $login = $this->generateUrl('ColectaBackendLotteryIndex');
        return new RedirectResponse($login);
    }
    
    public function checkRepeatedShred($em, $shred, $start, $end)
    {
        $compareShred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.lotteryCampaign = :campaign AND s.start <= :end AND s.end >= :start AND s.date > :date ORDER BY s.date ASC')->setParameters(array('campaign'=>$shred->getLotteryCampaign(), 'start'=>$start, 'end'=>$end, 'date'=>$shred->getDate()))->getResult();
        
        if($shred->getReturned() and !count($compareShred)) 
        {
            return false;
        }
        elseif(!count($compareShred))
        {
            return true;
        }
        
        $compareShred = $compareShred[0];
        
        if($compareShred->getReturned())
        {
            if($compareShred->getStart() <= $start and $compareShred->getEnd() >= $end)
            {
                return $this->checkRepeatedShred($em, $compareShred, $start, $end);
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }
    
    public function removeShredAction($shred_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $shred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.id = :id ORDER BY s.date ASC')->setParameter('id',$shred_id)->getResult();
        
        if(!count($shred))
        {
            $this->get('session')->setFlash('error', 'No se ha encontrado la referencia.');
            
            $login = $this->generateUrl('ColectaBackendLotteryIndex');
            return new RedirectResponse($login);
        }
        
        $em->remove($shred[0]);
        $em->flush();
        
        $this->get('session')->setFlash('success', 'Eliminada correctamente.');
        
        $login = $this->generateUrl('ColectaBackendLotteryIndex');
        return new RedirectResponse($login);
    }
    
    public function returnShredAction($shred_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->get('request')->request;
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $shred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.id = :id AND s.returned = 0 ORDER BY s.date ASC')->setParameter('id',$shred_id)->getResult();
        
        if(!count($shred))
        {
            $this->get('session')->setFlash('error', 'No se ha encontrado la referencia.');
            
            $login = $this->generateUrl('ColectaBackendLotteryIndex');
            return new RedirectResponse($login);
        }
        
        $shred = $shred[0];
        
        $newShred = new LotteryShred();
        $newShred->setUser($shred->getUser());
        $newShred->setStart($request->get('start'));
        $newShred->setEnd($request->get('end'));
        $newShred->setDate(new \DateTime('now'));
        $newShred->setPaid($shred->getPaid());
        $newShred->setReturned(1);
        $newShred->setLotteryCampaign($shred->getLotteryCampaign());
        
        $em->persist($newShred); 
        $em->flush();
        
        $this->get('session')->setFlash('success', 'Devolución realizada.');
        
        $login = $this->generateUrl('ColectaBackendLotteryIndex');
        return new RedirectResponse($login);
    }
}