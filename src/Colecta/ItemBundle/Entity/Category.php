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
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var datetime $lastchange
     *
     * @ORM\Column(name="lastchange", type="datetime")
     */
    private $lastchange;

    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="category")
     */
    private $items;


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
}