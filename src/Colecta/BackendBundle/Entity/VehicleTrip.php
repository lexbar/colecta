<?php

namespace Colecta\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\BackendBundle\Entity\VehicleTrip
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class VehicleTrip
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $event
     *
     * @ORM\Column(name="event", type="string", length=255)
     */
    private $event;

    /**
     * @var string $confirmed
     *
     * @ORM\Column(name="confirmed", type="string", length=255)
     */
    private $confirmed;

    /**
     * @var string $requested
     *
     * @ORM\Column(name="requested", type="string", length=255)
     */
    private $requested;

    /**
     * @var string $vehicle
     *
     * @ORM\Column(name="vehicle", type="string", length=255)
     */
    private $vehicle;


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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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
     * Set confirmed
     *
     * @param string $confirmed
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
    }

    /**
     * Get confirmed
     *
     * @return string 
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set requested
     *
     * @param string $requested
     */
    public function setRequested($requested)
    {
        $this->requested = $requested;
    }

    /**
     * Get requested
     *
     * @return string 
     */
    public function getRequested()
    {
        return $this->requested;
    }

    /**
     * Set vehicle
     *
     * @param string $vehicle
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * Get vehicle
     *
     * @return string 
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }
}