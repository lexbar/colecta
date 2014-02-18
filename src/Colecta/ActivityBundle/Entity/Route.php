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
     * @var text $text
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text = '';

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
    
    //Heritage
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var boolean
     */
    protected $part;

    /**
     * @var string
     */
    protected $summary;

    /**
     * @var string
     */
    protected $tagwords;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var boolean
     */
    protected $allowComments;

    /**
     * @var boolean
     */
    protected $draft;

    /**
     * @var \Colecta\ActivityBundle\Entity\Category
     */
    protected $category;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $relatedto;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $relatedfrom;

    /**
     * @var \Colecta\UserBundle\Entity\User
     */
    protected $author;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $editors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $likers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $comments;


    public function __toString() 
    {
        return $this->getName();
    }
    
    public function getType()
    {
        return 'Activity/Route';
    }
    
    public function getViewPath()
    {
        return 'ColectaRouteView';
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->trackpoints = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set text
     *
     * @param string $text
     * @return Route
     */
    public function setText($text)
    {
        $text = str_replace(array("<br>","<br />"),"\n",str_replace("\n",'',$text));
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set distance
     *
     * @param float $distance
     * @return Route
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    
        return $this;
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
     * @return Route
     */
    public function setUphill($uphill)
    {
        $this->uphill = $uphill;
    
        return $this;
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
     * @return Route
     */
    public function setDownhill($downhill)
    {
        $this->downhill = $downhill;
    
        return $this;
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
     * @return Route
     */
    public function setTime($time)
    {
        $this->time = $time;
    
        return $this;
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
     * @return Route
     */
    public function setAvgspeed($avgspeed)
    {
        $this->avgspeed = $avgspeed;
    
        return $this;
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
     * @return Route
     */
    public function setMaxspeed($maxspeed)
    {
        $this->maxspeed = $maxspeed;
    
        return $this;
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
     * @param integer $minheight
     * @return Route
     */
    public function setMinheight($minheight)
    {
        $this->minheight = $minheight;
    
        return $this;
    }

    /**
     * Get minheight
     *
     * @return integer 
     */
    public function getMinheight()
    {
        return $this->minheight;
    }

    /**
     * Set maxheight
     *
     * @param integer $maxheight
     * @return Route
     */
    public function setMaxheight($maxheight)
    {
        $this->maxheight = $maxheight;
    
        return $this;
    }

    /**
     * Get maxheight
     *
     * @return integer 
     */
    public function getMaxheight()
    {
        return $this->maxheight;
    }

    /**
     * Set isloop
     *
     * @param boolean $isloop
     * @return Route
     */
    public function setIsloop($isloop)
    {
        $this->isloop = $isloop;
    
        return $this;
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
     * @return Route
     */
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
    
        return $this;
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
     * @return Route
     */
    public function setIBP($iBP)
    {
        $this->IBP = $iBP;
    
        return $this;
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
     * @return Route
     */
    public function setSourcefile($sourcefile)
    {
        $this->sourcefile = $sourcefile;
    
        return $this;
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
     * @param \Colecta\ActivityBundle\Entity\Activity $activity
     * @return Route
     */
    public function setActivity(\Colecta\ActivityBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;
    
        return $this;
    }

    /**
     * Get activity
     *
     * @return \Colecta\ActivityBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Add trackpoints
     *
     * @param \Colecta\ActivityBundle\Entity\RouteTrackpoint $trackpoints
     * @return Route
     */
    public function addTrackpoint(\Colecta\ActivityBundle\Entity\RouteTrackpoint $trackpoints)
    {
        $this->trackpoints[] = $trackpoints;
    
        return $this;
    }

    /**
     * Remove trackpoints
     *
     * @param \Colecta\ActivityBundle\Entity\RouteTrackpoint $trackpoints
     */
    public function removeTrackpoint(\Colecta\ActivityBundle\Entity\RouteTrackpoint $trackpoints)
    {
        $this->trackpoints->removeElement($trackpoints);
    }

    /**
     * Get trackpoints
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTrackpoints()
    {
        return $this->trackpoints;
    }
}