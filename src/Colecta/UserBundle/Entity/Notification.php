<?php

namespace Colecta\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\UserBundle\Entity\Notification
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Notification
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
     * @var string $what
     *
     * @ORM\Column(name="what", type="string", length=15)
     */
    protected $what; // comment / reply / assist / unassist

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;
    
    /**
     * @var boolean $dismiss
     *
     * @ORM\Column(name="dismiss", type="boolean")
     */
    protected $dismiss;
    
    /**
    * @ORM\ManyToOne(targetEntity="User")
    */
    protected $user;
    
    /**
    * @ORM\ManyToOne(targetEntity="Colecta\ItemBundle\Entity\Item")
    */
    protected $item;
    
    /**
    * @ORM\ManyToOne(targetEntity="User")
    */
    protected $who;
    
    /**
    * @var integer $pluspeople
    *
    * @ORM\Column(name="pluspeople", type="integer")
    */
    protected $pluspeople = 0;


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
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set user
     *
     * @param Colecta\UserBundle\Entity\User $user
     */
    public function setUser(\Colecta\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Colecta\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set dismiss
     *
     * @param boolean $dismiss
     */
    public function setDismiss($dismiss)
    {
        $this->dismiss = $dismiss;
    }

    /**
     * Get dismiss
     *
     * @return boolean 
     */
    public function getDismiss()
    {
        return $this->dismiss;
    }

    /**
     * Set what
     *
     * @param string $what
     * @return Notification
     */
    public function setWhat($what)
    {
        $this->what = $what;
    
        return $this;
    }

    /**
     * Get what
     *
     * @return string 
     */
    public function getWhat()
    {
        return $this->what;
    }

    /**
     * Set item
     *
     * @param \Colecta\ItemBundle\Entity\Item $item
     * @return Notification
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

    /**
     * Set who
     *
     * @param \Colecta\UserBundle\Entity\User $who
     * @return Notification
     */
    public function setWho(\Colecta\UserBundle\Entity\User $who = null)
    {
        $this->who = $who;
    
        return $this;
    }

    /**
     * Get who
     *
     * @return \Colecta\UserBundle\Entity\User 
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * Set pluspeople
     *
     * @param integer $pluspeople
     * @return Notification
     */
    public function setPluspeople($pluspeople)
    {
        $this->pluspeople = $pluspeople;
    
        return $this;
    }

    /**
     * Get pluspeople
     *
     * @return integer 
     */
    public function getPluspeople()
    {
        return $this->pluspeople;
    }
}