<?php

namespace Colecta\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ActivityBundle\Entity\Route
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Route extends \Colecta\ItemBundle\Entity\Item
{

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @var float $distance
     *
     * @ORM\Column(name="distance", type="float")
     */
    protected $distance;

    /**
     * @var integer $uphill
     *
     * @ORM\Column(name="uphill", type="integer")
     */
    protected $uphill;

    /**
     * @var integer $downhill
     *
     * @ORM\Column(name="downhill", type="integer")
     */
    protected $downhill;

    /**
     * @var integer $time
     *
     * @ORM\Column(name="time", type="integer")
     */
    protected $time;

    /**
     * @var float $avgspeed
     *
     * @ORM\Column(name="avgspeed", type="float")
     */
    protected $avgspeed;

    /**
     * @var float $maxspeed
     *
     * @ORM\Column(name="maxspeed", type="float")
     */
    protected $maxspeed;

    /**
     * @var smallint $minheight
     *
     * @ORM\Column(name="minheight", type="smallint")
     */
    protected $minheight;

    /**
     * @var smallint $maxheight
     *
     * @ORM\Column(name="maxheight", type="smallint")
     */
    protected $maxheight;

    /**
     * @var boolean $isloop
     *
     * @ORM\Column(name="isloop", type="boolean")
     */
    protected $isloop;

    /**
     * @var string $difficulty
     *
     * @ORM\Column(name="difficulty", type="string", length=12)
     */
    protected $difficulty;

    /**
     * @var string $IBP
     *
     * @ORM\Column(name="IBP", type="string", length=10)
     */
    protected $IBP;

    /**
     * @var string $sourcefile
     *
     * @ORM\Column(name="sourcefile", type="string", length=255)
     */
    protected $sourcefile;

    /**
    * @ORM\ManyToOne(targetEntity="Activity")
    * @ORM\JoinColumn(name="activity_id", referencedColumnName="id") 
    */
    protected $activity;

    /**
     * @ORM\OneToMany(targetEntity="RouteTrackpoint", mappedBy="route", cascade={"persist", "remove"})
     */
    protected $trackpoints;

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set distance
     *
     * @param float $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    /**
     * Get distance
     *
     * @return float 
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set uphill
     *
     * @param integer $uphill
     */
    public function setUphill($uphill)
    {
        $this->uphill = $uphill;
    }

    /**
     * Get uphill
     *
     * @return integer 
     */
    public function getUphill()
    {
        return $this->uphill;
    }

    /**
     * Set downhill
     *
     * @param integer $downhill
     */
    public function setDownhill($downhill)
    {
        $this->downhill = $downhill;
    }

    /**
     * Get downhill
     *
     * @return integer 
     */
    public function getDownhill()
    {
        return $this->downhill;
    }

    /**
     * Set time
     *
     * @param integer $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Get time
     *
     * @return integer 
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set avgspeed
     *
     * @param float $avgspeed
     */
    public function setAvgspeed($avgspeed)
    {
        $this->avgspeed = $avgspeed;
    }

    /**
     * Get avgspeed
     *
     * @return float 
     */
    public function getAvgspeed()
    {
        return $this->avgspeed;
    }

    /**
     * Set maxspeed
     *
     * @param float $maxspeed
     */
    public function setMaxspeed($maxspeed)
    {
        $this->maxspeed = $maxspeed;
    }

    /**
     * Get maxspeed
     *
     * @return float 
     */
    public function getMaxspeed()
    {
        return $this->maxspeed;
    }

    /**
     * Set minheight
     *
     * @param smallint $minheight
     */
    public function setMinheight($minheight)
    {
        $this->minheight = $minheight;
    }

    /**
     * Get minheight
     *
     * @return smallint 
     */
    public function getMinheight()
    {
        return $this->minheight;
    }

    /**
     * Set maxheight
     *
     * @param smallint $maxheight
     */
    public function setMaxheight($maxheight)
    {
        $this->maxheight = $maxheight;
    }

    /**
     * Get maxheight
     *
     * @return smallint 
     */
    public function getMaxheight()
    {
        return $this->maxheight;
    }

    /**
     * Set isloop
     *
     * @param boolean $isloop
     */
    public function setIsloop($isloop)
    {
        $this->isloop = $isloop;
    }

    /**
     * Get isloop
     *
     * @return boolean 
     */
    public function getIsloop()
    {
        return $this->isloop;
    }

    /**
     * Set difficulty
     *
     * @param string $difficulty
     */
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
    }

    /**
     * Get difficulty
     *
     * @return string 
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * Set IBP
     *
     * @param string $iBP
     */
    public function setIBP($iBP)
    {
        $this->IBP = $iBP;
    }

    /**
     * Get IBP
     *
     * @return string 
     */
    public function getIBP()
    {
        return $this->IBP;
    }

    /**
     * Set sourcefile
     *
     * @param string $sourcefile
     */
    public function setSourcefile($sourcefile)
    {
        $this->sourcefile = $sourcefile;
    }

    /**
     * Get sourcefile
     *
     * @return string 
     */
    public function getSourcefile()
    {
        return $this->sourcefile;
    }

    /**
     * Set activity
     *
     * @param string $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get activity
     *
     * @return string 
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set trackpoints
     *
     * @param string $trackpoints
     */
    public function setTrackpoints($trackpoints)
    {
        $this->trackpoints = $trackpoints;
    }

    /**
     * Get trackpoints
     *
     * @return string 
     */
    public function getTrackpoints()
    {
        return $this->trackpoints;
    }
    public function __construct()
    {
        $this->trackpoints = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add trackpoints
     *
     * @param Colecta\ActivityBundle\Entity\RouteTrackpoint $trackpoints
     */
    public function addRouteTrackpoint(\Colecta\ActivityBundle\Entity\RouteTrackpoint $trackpoints)
    {
        $this->trackpoints[] = $trackpoints;
    }
    public function __toString() 
    {
        return $this->getName();
    }
    
    public function getType()
    {
        return 'Activity/Route';
    }
}