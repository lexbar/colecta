<?php

namespace Colecta\ItemBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ItemBundle\Entity\Relation
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Relation
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
    * @ORM\ManyToOne(targetEntity="Item")
    * @ORM\JoinColumn(name="itemto_id", referencedColumnName="id", onDelete="CASCADE") 
    */
    protected $itemto;

    /**
    * @ORM\ManyToOne(targetEntity="Item")
    * @ORM\JoinColumn(name="itemfrom_id", referencedColumnName="id", onDelete="CASCADE") 
    */
    protected $itemfrom;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE") 
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
     * Set itemto
     *
     * @param string $itemto
     */
    public function setItemto($itemto)
    {
        $this->itemto = $itemto;
    }

    /**
     * Get itemto
     *
     * @return string 
     */
    public function getItemto()
    {
        return $this->itemto;
    }

    /**
     * Set itemfrom
     *
     * @param string $itemfrom
     */
    public function setItemfrom($itemfrom)
    {
        $this->itemfrom = $itemfrom;
    }

    /**
     * Get itemfrom
     *
     * @return string 
     */
    public function getItemfrom()
    {
        return $this->itemfrom;
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
}