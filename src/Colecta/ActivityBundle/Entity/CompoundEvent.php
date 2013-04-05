<?php

namespace Colecta\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ActivityBundle\Entity\CompoundEvent
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class CompoundEvent extends \Colecta\ActivityBundle\Entity\Event
{
    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="compound")
     */
    protected $events;

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

    /**
     * @var \DateTime
     */
    protected $dateini;

    /**
     * @var \DateTime
     */
    protected $dateend;

    /**
     * @var boolean
     */
    protected $showhours;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var float
     */
    protected $distance;

    /**
     * @var integer
     */
    protected $uphill;

    /**
     * @var integer
     */
    protected $downhill;

    /**
     * @var string
     */
    protected $difficulty;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var \Colecta\ActivityBundle\Entity\Activity
     */
    protected $activity;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $assistances;

    /**
     * @var \Colecta\ActivityBundle\Entity\CompoundEvent
     */
    protected $compound;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add events
     *
     * @param \Colecta\ActivityBundle\Entity\Event $events
     * @return CompoundEvent
     */
    public function addEvent(\Colecta\ActivityBundle\Entity\Event $events)
    {
        $this->events[] = $events;
    
        return $this;
    }

    /**
     * Remove events
     *
     * @param \Colecta\ActivityBundle\Entity\Event $events
     */
    public function removeEvent(\Colecta\ActivityBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }
}