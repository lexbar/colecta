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
     * @var string $text
     *
     * @ORM\Column(name="text", type="string", length=255)
     */
    protected $text;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
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
}