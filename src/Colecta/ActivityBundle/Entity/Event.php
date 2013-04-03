<?php

namespace Colecta\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ActivityBundle\Entity\Event
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Event extends \Colecta\ItemBundle\Entity\Item
{
    /**
     * @var datetime $dateini
     *
     * @ORM\Column(name="dateini", type="datetime")
     */
    protected $dateini;

    /**
     * @var datetime $dateend
     *
     * @ORM\Column(name="dateend", type="datetime")
     */
    protected $dateend;

    /**
     * @var boolean $showhours
     *
     * @ORM\Column(name="showhours", type="boolean")
     */
    protected $showhours;

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
     * @var string $difficulty
     *
     * @ORM\Column(name="difficulty", type="string", length=12)
     */
    protected $difficulty;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=20)
     */
    protected $status;

    /**
    * @ORM\ManyToOne(targetEntity="Activity")
    * @ORM\JoinColumn(name="activity_id", referencedColumnName="id") 
    */
    protected $activity;

    /**
     * @ORM\OneToMany(targetEntity="EventAssistance", mappedBy="event")
     */
    protected $assistances;
    
    /**
    * @ORM\ManyToOne(targetEntity="CompoundEvent")
    * @ORM\JoinColumn(name="compound_id", referencedColumnName="id") 
    */
    protected $compound;

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
    

    public function getTime()
    {
        //Number of secconds between both dates
        if($this->getDateend() < $this->getDateini()) return 0;
        return $this->getDateend()->format('U') - $this->getDateini()->format('U');
    }
    
    /**
     * Has assistances
     *
     * @return boolean 
     */
    public function hasAssistances()
    {
        return count($this->assistances) > 0;
    }
    
    public function __construct()
    {
        $this->assistances = new \Doctrine\Common\Collections\ArrayCollection();
    }
        
    public function getType()
    {
        return 'Activity/Event';
    }
    
    public function happened()
    {
        $now = new \DateTime('now');
        return $now >= $this->getDateini();
    }

    /**
     * Set dateini
     *
     * @param \DateTime $dateini
     * @return Event
     */
    public function setDateini($dateini)
    {
        $this->dateini = $dateini;
    
        return $this;
    }

    /**
     * Get dateini
     *
     * @return \DateTime 
     */
    public function getDateini()
    {
        return $this->dateini;
    }

    /**
     * Set dateend
     *
     * @param \DateTime $dateend
     * @return Event
     */
    public function setDateend($dateend)
    {
        $this->dateend = $dateend;
    
        return $this;
    }

    /**
     * Get dateend
     *
     * @return \DateTime 
     */
    public function getDateend()
    {
        return $this->dateend;
    }

    /**
     * Set showhours
     *
     * @param boolean $showhours
     * @return Event
     */
    public function setShowhours($showhours)
    {
        $this->showhours = $showhours;
    
        return $this;
    }

    /**
     * Get showhours
     *
     * @return boolean 
     */
    public function getShowhours()
    {
        return $this->showhours;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set distance
     *
     * @param float $distance
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * Set difficulty
     *
     * @param string $difficulty
     * @return Event
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
     * Set status
     *
     * @param string $status
     * @return Event
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set activity
     *
     * @param \Colecta\ActivityBundle\Entity\Activity $activity
     * @return Event
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
     * Add assistances
     *
     * @param \Colecta\ActivityBundle\Entity\EventAssistance $assistances
     * @return Event
     */
    public function addAssistance(\Colecta\ActivityBundle\Entity\EventAssistance $assistances)
    {
        $this->assistances[] = $assistances;
    
        return $this;
    }

    /**
     * Remove assistances
     *
     * @param \Colecta\ActivityBundle\Entity\EventAssistance $assistances
     */
    public function removeAssistance(\Colecta\ActivityBundle\Entity\EventAssistance $assistances)
    {
        $this->assistances->removeElement($assistances);
    }

    /**
     * Get assistances
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAssistances()
    {
        return $this->assistances;
    }

    /**
     * Set compound
     *
     * @param \Colecta\ActivityBundle\Entity\CompoundEvent $compound
     * @return Event
     */
    public function setCompound(\Colecta\ActivityBundle\Entity\CompoundEvent $compound = null)
    {
        $this->compound = $compound;
    
        return $this;
    }

    /**
     * Get compound
     *
     * @return \Colecta\ActivityBundle\Entity\CompoundEvent 
     */
    public function getCompound()
    {
        return $this->compound;
    }
}