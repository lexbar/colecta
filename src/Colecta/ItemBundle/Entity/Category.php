<?php

namespace Colecta\ItemBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ItemBundle\Entity\Category
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Category
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @var datetime $lastchange
     *
     * @ORM\Column(name="lastchange", type="datetime")
     */
    protected $lastchange;

    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="category")
     */
    protected $items;
    
    /**
     * @var integer $posts
     *
     * @ORM\Column(name="posts", type="integer")
     */
    protected $posts = 0;
    
    /**
     * @var integer $events
     *
     * @ORM\Column(name="events", type="integer")
     */
    protected $events = 0;
    
    /**
     * @var integer $routes
     *
     * @ORM\Column(name="routes", type="integer")
     */
    protected $routes = 0;
    
    /**
     * @var integer $places
     *
     * @ORM\Column(name="places", type="integer")
     */
    protected $places = 0;
    
    /**
     * @var integer $files
     *
     * @ORM\Column(name="files", type="integer")
     */
    protected $files = 0;

    
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
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
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
     * Set lastchange
     *
     * @param datetime $lastchange
     */
    public function setLastchange($lastchange)
    {
        $this->lastchange = $lastchange;
    }

    /**
     * Get lastchange
     *
     * @return datetime 
     */
    public function getLastchange()
    {
        return $this->lastchange;
    }

    /**
     * Set items
     *
     * @param string $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * Get items
     *
     * @return string 
     */
    public function getItems()
    {
        return $this->items;
    }
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add items
     *
     * @param Colecta\ItemBundle\Entity\Item $items
     */
    public function addItem(\Colecta\ItemBundle\Entity\Item $items)
    {
        $this->items[] = $items;
    }

    /**
     * Remove items
     *
     * @param \Colecta\ItemBundle\Entity\Item $items
     */
    public function removeItem(\Colecta\ItemBundle\Entity\Item $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Set posts
     *
     * @param integer $posts
     * @return Category
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    
        return $this;
    }

    /**
     * Get posts
     *
     * @return integer 
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Set events
     *
     * @param integer $events
     * @return Category
     */
    public function setEvents($events)
    {
        $this->events = $events;
    
        return $this;
    }

    /**
     * Get events
     *
     * @return integer 
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Set routes
     *
     * @param integer $routes
     * @return Category
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    
        return $this;
    }

    /**
     * Get routes
     *
     * @return integer 
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set places
     *
     * @param integer $places
     * @return Category
     */
    public function setPlaces($places)
    {
        $this->places = $places;
    
        return $this;
    }

    /**
     * Get places
     *
     * @return integer 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Set files
     *
     * @param integer $files
     * @return Category
     */
    public function setFiles($files)
    {
        $this->files = $files;
    
        return $this;
    }

    /**
     * Get files
     *
     * @return integer 
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    public function getItemsCount()
    {
        return intval($this->getPosts()) + intval($this->getEvents()) + intval($this->getRoutes()) + intval($this->getPlaces()) + intval($this->getFiles());
    }
    
    public function sortedTypes()
    {
        $types = array(
                        'posts' => $this->getPosts(),
                        'events' => $this->getEvents(),
                        'routes' => $this->getRoutes(),
                        'places' => $this->getPlaces(),
                        'files' => $this->getFiles(),
        );
        
        arsort($types);
        
        return $types;
    }
}