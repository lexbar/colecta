<?php

namespace Colecta\UserBundle\Service;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
	
	protected $router;
	protected $security;
	protected $session;
	
	public function __construct(Router $router, SecurityContext $security, $session)
	{
		$this->router = $router;
		$this->security = $security;
		$this->session = $session;
	}
	
	public function onAuthenticationSuccess(Request $request, TokenInterface $token)
	{
		
		/*if ($this->security->isGranted('ROLE_SUPER_ADMIN'))
		{
			$response = new RedirectResponse($this->router->generate('category_index'));			
		}
		elseif ($this->security->isGranted('ROLE_ADMIN'))
		{
			$response = new RedirectResponse($this->router->generate('category_index'));
		} 
		elseif ($this->security->isGranted('ROLE_USER'))
		{*/
		
            //Set last access
            $this->session->set('sinceLastVisit',$this->security->getToken()->getUser()->getLastAccess());
            
			// redirect the user to where they were before the login process begun.
			$referer_url = $request->headers->get('referer');
						
			$response = new RedirectResponse($referer_url);
		/*}*/
			
		return $response;
	}
	
}