<?php

namespace Colecta\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PointsCondition
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PointsCondition
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
     * @var string
     *
     * @ORM\Column(name="requirement", type="string", length=15)
     */
    private $requirement;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\ActivityBundle\Entity\Activity")
    */
    private $activity;

    /**
    * @ORM\ManyToOne(targetEntity="Role")
    */
    private $role;

    /**
     * @var string
     *
     * @ORM\Column(name="operator", type="string", length=1)
     */
    private $operator;

    /**
     * @var integer
     *
     * @ORM\Column(name="value", type="integer")
     */
    private $value;

    /**
     * @var boolean
     *
     * @ORM\Column(name="gather", type="boolean")
     */
    private $gather;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
    * @ORM\ManyToOne(targetEntity="User")
    */
    private $author;



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
     * Set requirement
     *
     * @param string $requirement
     * @return PointsCondition
     */
    public function setRequirement($requirement)
    {
        $this->requirement = $requirement;
    
        return $this;
    }

    /**
     * Get requirement
     *
     * @return string 
     */
    public function getRequirement()
    {
        return $this->requirement;
    }

    /**
     * Set operator
     *
     * @param string $operator
     * @return PointsCondition
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    
        return $this;
    }

    /**
     * Get operator
     *
     * @return string 
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set value
     *
     * @param integer $value
     * @return PointsCondition
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set gather
     *
     * @param boolean $gather
     * @return PointsCondition
     */
    public function setGather($gather)
    {
        $this->gather = $gather;
    
        return $this;
    }

    /**
     * Get gather
     *
     * @return boolean 
     */
    public function getGather()
    {
        return $this->gather;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return PointsCondition
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    
        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return PointsCondition
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
     * Set activity
     *
     * @param \Colecta\ActivityBundle\Entity\Activity $activity
     * @return PointsCondition
     */
    public function setActivity(\Colecta\ActivityBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;
    
        return $this;
    }

    /**
     * Get activity
     *
     * @return \Colecta\ActivityBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set role
     *
     * @param \Colecta\UserBundle\Entity\Role $role
     * @return PointsCondition
     */
    public function setRole(\Colecta\UserBundle\Entity\Role $role = null)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return \Colecta\UserBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set author
     *
     * @param \Colecta\UserBundle\Entity\User $author
     * @return PointsCondition
     */
    public function setAuthor(\Colecta\UserBundle\Entity\User $author = null)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * Get author
     *
     * @return \Colecta\UserBundle\Entity\User 
     */
    public function getAuthor()
    {
        return $this->author;
    }
}