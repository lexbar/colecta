<?php

namespace Colecta\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ActivityBundle\Entity\EventAssistance
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class EventAssistance
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $confirmed
     *
     * @ORM\Column(name="confirmed", type="boolean")
     */
    protected $confirmed;
    
    /**
     * @var float $km
     *
     * @ORM\Column(name="km", type="float")
     */
    protected $km;

    /**
    * @ORM\ManyToOne(targetEntity="Event")
    * @ORM\JoinColumn(name="event_id", referencedColumnName="id") 
    */
    protected $event;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id") 
    */
    protected $user;


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
     * Set confirmed
     *
     * @param boolean $confirmed
     * @return EventAssistance
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
    
        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean 
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set km
     *
     * @param float $km
     * @return EventAssistance
     */
    public function setKm($km)
    {
        $this->km = $km;
    
        return $this;
    }

    /**
     * Get km
     *
     * @return float 
     */
    public function getKm()
    {
        return $this->km;
    }

    /**
     * Set event
     *
     * @param \Colecta\ActivityBundle\Entity\Event $event
     * @return EventAssistance
     */
    public function setEvent(\Colecta\ActivityBundle\Entity\Event $event = null)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return \Colecta\ActivityBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user
     *
     * @param \Colecta\UserBundle\Entity\User $user
     * @return EventAssistance
     */
    public function setUser(\Colecta\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Colecta\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}