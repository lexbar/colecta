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
     * @ORM\Column(name="ip", type="bigint")
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
     * @ORM\Column(name="type", type="string", length=20)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="ref", type="string", length=255)
     */
    private $ref;

    /**
     * @var string
     *
     * @ORM\Column(name="osName", type="string", length=25)
     */
    private $osName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="osVersion", type="string", length=20)
     */
    private $osVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="browserName", type="string", length=25)
     */
    private $browserName;

    /**
     * @var string
     *
     * @ORM\Column(name="browserVersion", type="string", length=20)
     */
    private $browserVersion;
    
    /**
     * @var string
     *
     * @ORM\Column(name="device", type="string", length=30)
     */
    private $device;
    
    //FROM HERE ON, THE DATA IS COLLECTED IN A SECCOND PHASE VIA JAVASCRIPT

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
     * Set browserName
     *
     * @param string $browser
     * @return Visit
     */
    public function setBrowserName($browserName)
    {
        $this->browserName = $browserName;
    
        return $this;
    }

    /**
     * Get browserName
     *
     * @return string 
     */
    public function getBrowserName()
    {
        return $this->browserName;
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

    /**
     * Set osName
     *
     * @param string $osName
     * @return Visit
     */
    public function setOsName($osName)
    {
        $this->osName = $osName;
    
        return $this;
    }

    /**
     * Get osName
     *
     * @return string 
     */
    public function getOsName()
    {
        return $this->osName;
    }

    /**
     * Set osVersion
     *
     * @param string $osVersion
     * @return Visit
     */
    public function setOsVersion($osVersion)
    {
        $this->osVersion = $osVersion;
    
        return $this;
    }

    /**
     * Get osVersion
     *
     * @return string 
     */
    public function getOsVersion()
    {
        return $this->osVersion;
    }

    /**
     * Set device
     *
     * @param string $device
     * @return Visit
     */
    public function setDevice($device)
    {
        $this->device = $device;
    
        return $this;
    }

    /**
     * Get device
     *
     * @return string 
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Visit
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function gettype()
    {
        return $this->type;
    }
}