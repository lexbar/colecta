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
     * Set event
     *
     * @param string $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return string 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
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
     */
    public function setKm($km)
    {
        $this->km = $km;
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
}