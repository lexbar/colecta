<?php

namespace Colecta\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ActivityBundle\Entity\RouteTrackpoint
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class RouteTrackpoint
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
     * @var float $latitude
     *
     * @ORM\Column(name="latitude", type="float")
     */
    protected $latitude;

    /**
     * @var float $longitude
     *
     * @ORM\Column(name="longitude", type="float")
     */
    protected $longitude;

    /**
     * @var smallint $altitude
     *
     * @ORM\Column(name="altitude", type="smallint")
     */
    protected $altitude;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
    * @ORM\ManyToOne(targetEntity="Route")
    * @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE") 
    */
    protected $route;

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
     * Set latitude
     *
     * @param float $latitude
     * @return RouteTrackpoint
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    
        return $this;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return RouteTrackpoint
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    
        return $this;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set altitude
     *
     * @param integer $altitude
     * @return RouteTrackpoint
     */
    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;
    
        return $this;
    }

    /**
     * Get altitude
     *
     * @return integer 
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return RouteTrackpoint
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
     * Set route
     *
     * @param \Colecta\ActivityBundle\Entity\Route $route
     * @return RouteTrackpoint
     */
    public function setRoute(\Colecta\ActivityBundle\Entity\Route $route = null)
    {
        $this->route = $route;
    
        return $this;
    }

    /**
     * Get route
     *
     * @return \Colecta\ActivityBundle\Entity\Route 
     */
    public function getRoute()
    {
        return $this->route;
    }
}