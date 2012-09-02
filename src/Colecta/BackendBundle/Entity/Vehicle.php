<?php

namespace Colecta\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\BackendBundle\Entity\Vehicle
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Vehicle
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
     * @var smallint $seats
     *
     * @ORM\Column(name="seats", type="smallint")
     */
    private $seats;

    /**
     * @var string $user
     *
     * @ORM\Column(name="user", type="string", length=255)
     */
    private $user;

    /**
     * @var string $vehicleTrips
     *
     * @ORM\Column(name="vehicleTrips", type="string", length=255)
     */
    private $vehicleTrips;


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
     * Set seats
     *
     * @param smallint $seats
     */
    public function setSeats($seats)
    {
        $this->seats = $seats;
    }

    /**
     * Get seats
     *
     * @return smallint 
     */
    public function getSeats()
    {
        return $this->seats;
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
     * Set vehicleTrips
     *
     * @param string $vehicleTrips
     */
    public function setVehicleTrips($vehicleTrips)
    {
        $this->vehicleTrips = $vehicleTrips;
    }

    /**
     * Get vehicleTrips
     *
     * @return string 
     */
    public function getVehicleTrips()
    {
        return $this->vehicleTrips;
    }
}