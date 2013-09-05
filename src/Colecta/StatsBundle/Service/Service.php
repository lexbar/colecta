<?php
namespace Colecta\StatsBundle\Service;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Colecta\StatsBundle\Entity\Visit;
use Colecta\StatsBundle\Service\UAParser\UAParser;

class Service
{
    private $request;
    private $doctrine;
    private $session;
    
    public function __construct($request, $doctrine, $session)
    {
        $this->request = $request;
        $this->doctrine = $doctrine;
        $this->session = $session;
    }
    
    public function recordVisit()
    {
        if($this->request->get('_route') != '_internal')
        {
            $em = $this->doctrine->getEntityManager();
            
            $visit = new Visit();
            
            $visit->setDate( new \DateTime('now') );
            $visit->setIp( sprintf("%u", ip2long($this->request->getClientIp())) );
            $visit->setRoute( $this->request->getRequestUri() );
            $visit->setType( $this->request->getRequestFormat() );
            if( isset( $_SERVER['HTTP_REFERER'] ) )
            {
                $visit->setRef( strval($_SERVER['HTTP_REFERER']) );
            }
            else
            {
                $visit->setRef( '' );
            }
            
            
            $parser = new UAParser;
            $ua = $parser->parse( $_SERVER['HTTP_USER_AGENT'] );
            
            $visit->setOsName( $ua->os->family );
            $visit->setOsVersion( $ua->os->toVersionString );
            $visit->setBrowserName( $ua->ua->family );
            $visit->setBrowserVersion( $ua->ua->toVersionString );
            $visit->setDevice( $ua->device->family );
            
            
            $em->persist($visit); 
            $em->flush();
            
            //this.session.set
        }
                    
        return true;
    }
}
?>