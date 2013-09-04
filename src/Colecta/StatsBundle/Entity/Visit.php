<?php

namespace Colecta\StatsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Visit
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Visit
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="ip", type="integer")
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=255)
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(name="ref", type="string", length=255)
     */
    private $ref;

    /**
     * @var float
     *
     * @ORM\Column(name="timeload", type="float")
     */
    private $timeload=0;

    /**
     * @var integer
     *
     * @ORM\Column(name="screenw", type="smallint")
     */
    private $screenw=0;

    /**
     * @var integer
     *
     * @ORM\Column(name="screenh", type="smallint")
     */
    private $screenh=0;

    /**
     * @var string
     *
     * @ORM\Column(name="os", type="string", length=15)
     */
    private $os='';

    /**
     * @var string
     *
     * @ORM\Column(name="browser", type="string", length=25)
     */
    private $browser='';

    /**
     * @var string
     *
     * @ORM\Column(name="browserVersion", type="string", length=20)
     */
    private $browserVersion='';

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=60)
     */
    private $location='';


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Visit
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set ip
     *
     * @param integer $ip
     * @return Visit
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    
        return $this;
    }

    /**
     * Get ip
     *
     * @return integer 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set route
     *
     * @param string $route
     * @return Visit
     */
    public function setRoute($route)
    {
        $this->route = $route;
    
        return $this;
    }

    /**
     * Get route
     *
     * @return string 
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set ref
     *
     * @param string $ref
     * @return Visit
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    
        return $this;
    }

    /**
     * Get ref
     *
     * @return string 
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set timeload
     *
     * @param float $timeload
     * @return Visit
     */
    public function setTimeload($timeload)
    {
        $this->timeload = $timeload;
    
        return $this;
    }

    /**
     * Get timeload
     *
     * @return float 
     */
    public function getTimeload()
    {
        return $this->timeload;
    }

    /**
     * Set screenw
     *
     * @param integer $screenw
     * @return Visit
     */
    public function setScreenw($screenw)
    {
        $this->screenw = $screenw;
    
        return $this;
    }

    /**
     * Get screenw
     *
     * @return integer 
     */
    public function getScreenw()
    {
        return $this->screenw;
    }

    /**
     * Set screenh
     *
     * @param integer $screenh
     * @return Visit
     */
    public function setScreenh($screenh)
    {
        $this->screenh = $screenh;
    
        return $this;
    }

    /**
     * Get screenh
     *
     * @return integer 
     */
    public function getScreenh()
    {
        return $this->screenh;
    }

    /**
     * Set os
     *
     * @param string $os
     * @return Visit
     */
    public function setOs($os)
    {
        $this->os = $os;
    
        return $this;
    }

    /**
     * Get os
     *
     * @return string 
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Set browser
     *
     * @param string $browser
     * @return Visit
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
    
        return $this;
    }

    /**
     * Get browser
     *
     * @return string 
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Set browserVersion
     *
     * @param string $browserVersion
     * @return Visit
     */
    public function setBrowserVersion($browserVersion)
    {
        $this->browserVersion = $browserVersion;
    
        return $this;
    }

    /**
     * Get browserVersion
     *
     * @return string 
     */
    public function getBrowserVersion()
    {
        return $this->browserVersion;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Visit
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }
}
