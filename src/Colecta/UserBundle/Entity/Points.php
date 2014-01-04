<?php

namespace Colecta\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Points
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Points
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="User")
    */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="points", type="integer")
     */
    private $points;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\ItemBundle\Entity\Item")
    * @ORM\JoinColumn(name="item_id", referencedColumnName="id", onDelete="CASCADE")
    */
    private $item;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * Set points
     *
     * @param integer $points
     * @return Points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    
        return $this;
    }

    /**
     * Get points
     *
     * @return integer 
     */
    public function getPoints()
    {
        return $this->points;
    }


    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Points
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
     * Set user
     *
     * @param \Colecta\UserBundle\Entity\User $user
     * @return Points
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

    /**
     * Set item
     *
     * @param \Colecta\ItemBundle\Entity\Item $item
     * @return Points
     */
    public function setItem(\Colecta\ItemBundle\Entity\Item $item = null)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return \Colecta\ItemBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->item;
    }
}