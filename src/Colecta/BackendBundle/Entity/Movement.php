<?php

namespace Colecta\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\BackendBundle\Entity\Movement
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Movement
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
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var float $balance
     *
     * @ORM\Column(name="balance", type="float")
     */
    private $balance;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string $concept
     *
     * @ORM\Column(name="concept", type="string", length=255)
     */
    private $concept;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id") 
    */
    private $user;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\BackendBundle\Entity\MovementCategory")
    * @ORM\JoinColumn(name="category", referencedColumnName="id") 
    */
    private $movementCategory;


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
     * Set amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set balance
     *
     * @param float $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * Get balance
     *
     * @return float 
     */
    public function getBalance()
    {
        return $this->balance;
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
     * Set concept
     *
     * @param string $concept
     */
    public function setConcept($concept)
    {
        $this->concept = $concept;
    }

    /**
     * Get concept
     *
     * @return string 
     */
    public function getConcept()
    {
        return $this->concept;
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

    /**
     * Set movementCategory
     *
     * @param string $movementCategory
     */
    public function setMovementCategory($movementCategory)
    {
        $this->movementCategory = $movementCategory;
    }

    /**
     * Get movementCategory
     *
     * @return string 
     */
    public function getMovementCategory()
    {
        return $this->movementCategory;
    }
}