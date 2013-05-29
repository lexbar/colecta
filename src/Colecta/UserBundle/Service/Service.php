<?php
namespace Colecta\UserBundle\Service;

use Symfony\Component\Security\Core\SecurityContextInterface;

class Service
{
    private $securityContext;
    private $doctrine;
    private $lastAccessDone = false;
    
    public function __construct(SecurityContextInterface $securityContext, $doctrine, $session)
    {
        $this->securityContext = $securityContext;
        $this->doctrine = $doctrine;
        $this->session = $session;
    }
    
    public function notDismissedNotifications($item = 0) //if item is set means this is called from the view of the item 
    {
        $em = $this->doctrine->getEntityManager();
        $query = $em->createQuery('SELECT n FROM ColectaUserBundle:Notification n WHERE n.user = :uid AND n.dismiss = 0 ORDER BY n.date DESC');
        $query->setParameter('uid', $this->securityContext->getToken()->getUser()->getId());
        
        $notifications = $query->getResult();
        
        if($item > 0)
        {
            for($i = 0; $i < count($notifications); $i++)
            {
                if($item == $notifications[$i]->getItem()->getId())
                {
                    $notifications[$i]->setDismiss(1);
                    $em->persist($notifications[$i]);
                    $em->flush();
                    
                    unset($notifications[$i]);
                    $notifications = array_values($notifications);
                    $i--;
                }
            }
        }
        
        return $notifications;
    }
    
    public function notDismissedMessages() 
    {
        $em = $this->doctrine->getEntityManager();
        $query = $em->createQuery('SELECT m FROM ColectaUserBundle:Message m WHERE m.destination = :uid AND m.dismiss = 0 ORDER BY m.date DESC');
        $query->setParameter('uid', $this->securityContext->getToken()->getUser()->getId());
        
        return $query->getResult();
    }
    
    public function sinceLastVisitItems()
    {
        if(!$this->securityContext->getToken())
        {
            return 0;
        }
        else
        {
            $em = $this->doctrine->getEntityManager();
            $slv = $this->session->get('sinceLastVisit');
            $user = $this->securityContext->getToken()->getUser();
            
            if(empty($slv) || $slv == 'dismiss') {
                $slv = $user->getLastAccess();
            }
            
            $query = $em->createQueryBuilder('ColectaItemBundle:Item')
                ->select('COUNT(i)')
                ->from('ColectaItemBundle:Item', 'i')
                ->leftJoin('i.comments', 'c')
                ->where('i.draft = 0 AND i.part = 0 AND (i.date > \''.$slv->format('Y-m-d H:i:s').'\' OR c.date > \''.$slv->format('Y-m-d H:i:s').'\')')
                ->orderBy('i.date', 'ASC')
                ->getQuery();
                
            return $query->getSingleScalarResult();
        }
    }
    
    public function lastAccess()
    {
        if(!$this->lastAccessDone)
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
            
            $this->lastAccessDone = true;
        }
       
    }
    
    public function sinceLastVisit()
    {
        if(!$this->securityContext->getToken())
        {
            return;
        }
        else
        {
            $user = $this->securityContext->getToken()->getUser();
        
            if($user == 'anon.')
            {
                return;
            }
        }
            
        $slv = $this->session->get('sinceLastVisit');
        
        if(empty($slv))
        {
            $this->session->set('sinceLastVisit',$user->getLastAccess());
        }
    }
}
?>