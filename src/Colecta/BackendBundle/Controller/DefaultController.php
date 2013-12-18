<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if($user == 'anon.' || !$user->getRole()->getSiteConfig())
        {
            $login = $this->generateUrl('userLogin');
            return new RedirectResponse($login);
        }
        
        if($user->getRole()->getSiteConfigSettings())
        {
            return $this->redirect($this->generateUrl('ColectaBackendSettingsIndex'));
        }
        elseif($user->getRole()->getSiteConfigUsers())
        {
            return $this->redirect($this->generateUrl('ColectaBackendLotteryIndex'));
        }
        elseif($user->getRole()->getSiteConfigPages())
        {
            return $this->redirect($this->generateUrl('ColectaBackendLotteryIndex'));
        }
        elseif($user->getRole()->getSiteConfigLottery())
        {
            return $this->redirect($this->generateUrl('ColectaBackendLotteryIndex'));
        }
        elseif($user->getRole()->getSiteConfigStats())
        {
            return $this->redirect($this->generateUrl('ColectaBackendLotteryIndex'));
        }
        elseif($user->getRole()->getSiteConfigPlan())
        {
            return $this->redirect($this->generateUrl('ColectaBackendLotteryIndex'));
        }
    }
}