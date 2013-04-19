<?php
namespace Colecta\UserBundle\Service;

use Symfony\Component\Security\Core\SecurityContextInterface;

class Service
{
    private $securityContext;
    private $doctrine;
    
    public function __construct(SecurityContextInterface $securityContext, $doctrine)
    {
        $this->securityContext = $securityContext;
        $this->doctrine = $doctrine;
    }
    
    public function notDismissedNotifications() 
    {
        $em = $this->doctrine->getEntityManager();
        $query = $em->createQuery('SELECT n FROM ColectaUserBundle:Notification n WHERE n.user = :uid AND n.dismiss = 0 ORDER BY n.date DESC');
        $query->setParameter('uid', $this->securityContext->getToken()->getUser()->getId());
        
        return $query->getResult();
    }
    
    public function notDismissedMessages() 
    {
        $em = $this->doctrine->getEntityManager();
        $query = $em->createQuery('SELECT m FROM ColectaUserBundle:Message m WHERE m.destination = :uid AND m.dismiss = 0 ORDER BY m.date DESC');
        $query->setParameter('uid', $this->securityContext->getToken()->getUser()->getId());
        
        return $query->getResult();
    }
    
    public function lastAccess()
    {
        $em = $this->doctrine->getEntityManager();
        
        if($this->securityContext->getToken())
        {
            $user = $this->securityContext->getToken()->getUser();
        
            if($user != 'anon.')
            {
                $user->setLastAccess(new \DateTime('now'));
            
                $em->persist($user); 
                $em->flush();
            }
        }
    }
}
?>