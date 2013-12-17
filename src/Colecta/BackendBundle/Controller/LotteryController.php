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
        
        usort($userShreds, function($a, $b)
            {
                return strcmp($a[0]->getUser()->getName(), $b[0]->getUser()->getName());
            }
        );
        
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
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $request = $this->get('request')->request;
        
        $start = intval($request->get('start'));
        $end = intval($request->get('end'));
        if($start > $end)
        {
            $start = intval($request->get('end'));
            $end = intval($request->get('start'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $targetUser = $em->createQuery('SELECT u FROM ColectaUserBundle:User u WHERE u.name = :uname')->setParameter('uname',$request->get('user'))->getResult();
        if(!count($targetUser))
        {
            $this->get('session')->setFlash('error', 'No se ha encontrado al usuario.');
            
            $login = $this->generateUrl('ColectaBackendLotteryIndex');
            return new RedirectResponse($login);
        }
        
        $targetUser = $targetUser[0];
                
        $campaign = $em->createQuery('SELECT c FROM ColectaIntranetBundle:LotteryCampaign c ORDER BY c.date DESC')->getSingleResult();
        
        $shred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.lotteryCampaign = :campaign AND s.start <= :end AND s.end >= :start ORDER BY s.date ASC')->setParameters(array('campaign'=>$campaign, 'start'=>$start, 'end'=>$end))->getResult();
        
        if(count($shred))
        {
            if($this->checkRepeatedShred($em, $shred[0], $start, $end))
            {
                $this->get('session')->setFlash('error', 'La selección es incompatible');
                
                $login = $this->generateUrl('ColectaBackendLotteryIndex');
                return new RedirectResponse($login);
            }
        }
                
        $newShred = new LotteryShred();
        $newShred->setUser($targetUser);
        $newShred->setStart($start);
        $newShred->setEnd($end);
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
        /* Search for other lottery shred contained between start and end */
        $compareShred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.lotteryCampaign = :campaign AND s.start <= :end AND s.end >= :start AND s.date > :date ORDER BY s.date ASC')->setParameters(array('campaign'=>$shred->getLotteryCampaign(), 'start'=>$start, 'end'=>$end, 'date'=>$shred->getDate()))->getResult();
        
        /* if previous found shred is a returned shred and there are no more shreds on those numbers, it is ok to go */
        if($shred->getReturned() and !count($compareShred)) 
        {
            return false;
        }
        /* if previous found shred is not returned and there are no more shreds on those numbers, this is a repeated request */
        elseif(!count($compareShred))
        {
            return true;
        }
        
        $compareShred = $compareShred[0];
        
        /* if this lottery shred is a returned one… */
        if($compareShred->getReturned())
        {
            /* if totally contains the start to end shred, keep checking further history, ok for now…  */
            if($compareShred->getStart() <= $start and $compareShred->getEnd() >= $end)
            {
                return $this->checkRepeatedShred($em, $compareShred, $start, $end);
            }
            /* if it is only a partial return, fail */
            else
            {
                return true;
            }
        }
        /* if it is not a returned one, fail */
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
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaBackendLotteryIndex');
        }
        
        return new RedirectResponse($referer);
    }
    
    public function returnShredAction($shred_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $request = $this->get('request')->request;
        
        $start = intval($request->get('start'));
        $end = intval($request->get('end'));
        if($start > $end)
        {
            $start = intval($request->get('end'));
            $end = intval($request->get('start'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $shred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.id = :id AND s.returned = 0 ORDER BY s.date DESC')->setParameter('id',$shred_id)->getResult();
        
        /* The lottery shred does not exist on database or is already a returned shred */
        if(!count($shred) || $shred[0]->getReturned())
        {
            $this->get('session')->setFlash('error', 'No se ha encontrado la referencia.');
            
            $referer = $this->get('request')->headers->get('referer');
        
            if(empty($referer))
            {
                $referer = $this->generateUrl('ColectaBackendLotteryIndex');
            }
            
            return new RedirectResponse($referer);
        }
        
        $shred = $shred[0];
        
        /* Check if there has been a return of this shred matching start and end */
        $returnedShred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.id > :id AND s.returned = 1 AND (s.start <= :end AND s.end >= :start) ORDER BY s.date DESC')->setParameters(array('id'=>$shred_id, 'start'=>$start, 'end'=>$end))->getResult();
        
        if(count($returnedShred))
        {
            $this->get('session')->setFlash('error', 'No puede realizarse la devolución porque coincide con otra anterior.');
            
            $referer = $this->get('request')->headers->get('referer');
        
            if(empty($referer))
            {
                $referer = $this->generateUrl('ColectaBackendLotteryIndex');
            }
            
            return new RedirectResponse($referer);
        }
        
        /* The return is out of bounds of original shred */
        if(intval($request->get('start')) < $shred->getStart() || intval($request->get('end')) > $shred->getEnd())
        {
            $this->get('session')->setFlash('error', 'Devolución fuera de rango.');
            
            $referer = $this->get('request')->headers->get('referer');
        
            if(empty($referer))
            {
                $referer = $this->generateUrl('ColectaBackendLotteryIndex');
            }
            
            return new RedirectResponse($referer);
        }
        
        /* Ok, create new shred */
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
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaBackendLotteryIndex');
        }
        
        return new RedirectResponse($referer);
    }
    
    public function unpaidShredAction($shred_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $shred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.id = :id AND s.returned = 0 ORDER BY s.date DESC')->setParameter('id',$shred_id)->getResult();
        
        /* The lottery shred does not exist on database or is already a returned shred */
        if(!count($shred) || !$shred[0]->getPaid())
        {
            $this->get('session')->setFlash('error', 'No se ha encontrado la referencia.');
            
            $referer = $this->get('request')->headers->get('referer');
        
            if(empty($referer))
            {
                $referer = $this->generateUrl('ColectaBackendLotteryIndex');
            }
            
            return new RedirectResponse($referer);
        }
        
        $shred = $shred[0];
        
        $shred->setPaid(0);
        
        $em->persist($shred); 
        $em->flush();
        
        $this->get('session')->setFlash('success', 'Marcado correctamente como no pagado.');
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaBackendLotteryIndex');
        }
        
        return new RedirectResponse($referer);
    }
    
    public function paidShredAction($shred_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $shred = $em->createQuery('SELECT s FROM ColectaIntranetBundle:LotteryShred s WHERE s.id = :id AND s.returned = 0 ORDER BY s.date DESC')->setParameter('id',$shred_id)->getResult();
        
        /* The lottery shred does not exist on database or is already a returned shred */
        if(!count($shred) || $shred[0]->getPaid())
        {
            $this->get('session')->setFlash('error', 'No se ha encontrado la referencia.');
            
            $referer = $this->get('request')->headers->get('referer');
        
            if(empty($referer))
            {
                $referer = $this->generateUrl('ColectaBackendLotteryIndex');
            }
            
            return new RedirectResponse($referer);
        }
        
        $shred = $shred[0];
        
        $shred->setPaid(1);
        
        $em->persist($shred); 
        $em->flush();
        
        $this->get('session')->setFlash('success', 'Marcado correctamente como no pagado.');
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaBackendLotteryIndex');
        }
        
        return new RedirectResponse($referer);
    }
}