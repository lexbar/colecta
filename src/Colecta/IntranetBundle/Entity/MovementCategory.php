<?php

namespace Colecta\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\IntranetBundle\Entity\MovementCategory
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MovementCategory
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
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="Movement", mappedBy="category")
     */
    private $movements;


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
     * Set movements
     *
     * @param string $movements
     */
    public function setMovements($movements)
    {
        $this->movements = $movements;
    }

    /**
     * Get movements
     *
     * @return string 
     */
    public function getMovements()
    {
        return $this->movements;
    }
    public function __construct()
    {
        $this->movements = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add movements
     *
     * @param Colecta\IntranetBundle\Entity\Movement $movements
     */
    public function addMovement(\Colecta\IntranetBundle\Entity\Movement $movements)
    {
        $this->movements[] = $movements;
    }
}