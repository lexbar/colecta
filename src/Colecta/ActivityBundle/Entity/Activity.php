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
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=25)
     */
    private $name;

    /**
     * @var string $routes
     *
     * @ORM\Column(name="routes", type="string", length=255)
     */
    private $routes;

    /**
     * @var string $events
     *
     * @ORM\Column(name="events", type="string", length=255)
     */
    private $events;


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
}