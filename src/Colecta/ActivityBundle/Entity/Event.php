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
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var datetime $dateini
     *
     * @ORM\Column(name="dateini", type="datetime")
     */
    private $dateini;

    /**
     * @var datetime $dateend
     *
     * @ORM\Column(name="dateend", type="datetime")
     */
    private $dateend;

    /**
     * @var boolean $showhours
     *
     * @ORM\Column(name="showhours", type="boolean")
     */
    private $showhours;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var float $distance
     *
     * @ORM\Column(name="distance", type="float")
     */
    private $distance;

    /**
     * @var integer $uphill
     *
     * @ORM\Column(name="uphill", type="integer")
     */
    private $uphill;

    /**
     * @var integer $downhill
     *
     * @ORM\Column(name="downhill", type="integer")
     */
    private $downhill;

    /**
     * @var string $difficulty
     *
     * @ORM\Column(name="difficulty", type="string", length=10)
     */
    private $difficulty;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=20)
     */
    private $status;

    /**
    * @ORM\ManyToOne(targetEntity="Activity")
    * @ORM\JoinColumn(name="activity_id", referencedColumnName="id") 
    */
    private $activity;

    /**
     * @ORM\OneToMany(targetEntity="EventAssistance", mappedBy="event")
     */
    private $assistances;


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
     * Set dateini
     *
     * @param datetime $dateini
     */
    public function setDateini($dateini)
    {
        $this->dateini = $dateini;
    }

    /**
     * Get dateini
     *
     * @return datetime 
     */
    public function getDateini()
    {
        return $this->dateini;
    }

    /**
     * Set dateend
     *
     * @param datetime $dateend
     */
    public function setDateend($dateend)
    {
        $this->dateend = $dateend;
    }

    /**
     * Get dateend
     *
     * @return datetime 
     */
    public function getDateend()
    {
        return $this->dateend;
    }

    /**
     * Set showhours
     *
     * @param boolean $showhours
     */
    public function setShowhours($showhours)
    {
        $this->showhours = $showhours;
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
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * Set assistances
     *
     * @param string $assistances
     */
    public function setAssistances($assistances)
    {
        $this->assistances = $assistances;
    }

    /**
     * Get assistances
     *
     * @return string 
     */
    public function getAssistances()
    {
        return $this->assistances;
    }
    public function __construct()
    {
        $this->assistances = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add assistances
     *
     * @param Colecta\ActivityBundle\Entity\EventAssistance $assistances
     */
    public function addEventAssistance(\Colecta\ActivityBundle\Entity\EventAssistance $assistances)
    {
        $this->assistances[] = $assistances;
    }
}