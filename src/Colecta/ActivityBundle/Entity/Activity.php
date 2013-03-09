<?php

namespace Colecta\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ActivityBundle\Entity\Activity
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Activity
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=25)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Route", mappedBy="activity")
     */
    protected $routes;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="activity")
     */
    protected $events;


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
     * Set routes
     *
     * @param string $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    /**
     * Get routes
     *
     * @return string 
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set events
     *
     * @param string $events
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }

    /**
     * Get events
     *
     * @return string 
     */
    public function getEvents()
    {
        return $this->events;
    }
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
    $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add routes
     *
     * @param Colecta\ActivityBundle\Entity\Route $routes
     */
    public function addRoute(\Colecta\ActivityBundle\Entity\Route $routes)
    {
        $this->routes[] = $routes;
    }

    /**
     * Add events
     *
     * @param Colecta\ActivityBundle\Entity\Event $events
     */
    public function addEvent(\Colecta\ActivityBundle\Entity\Event $events)
    {
        $this->events[] = $events;
    }
}