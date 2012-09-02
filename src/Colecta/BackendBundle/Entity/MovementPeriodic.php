<?php

namespace Colecta\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\BackendBundle\Entity\MovementPeriodic
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MovementPeriodic
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
     * @var string $monthdayrepeat
     *
     * @ORM\Column(name="monthdayrepeat", type="string", length=255)
     */
    private $monthdayrepeat;

    /**
     * @var string $weekdayrepeat
     *
     * @ORM\Column(name="weekdayrepeat", type="string", length=255)
     */
    private $weekdayrepeat;

    /**
     * @var string $monthrepeat
     *
     * @ORM\Column(name="monthrepeat", type="string", length=255)
     */
    private $monthrepeat;

    /**
     * @var string $concept
     *
     * @ORM\Column(name="concept", type="string", length=255)
     */
    private $concept;

    /**
     * @var string $user
     *
     * @ORM\Column(name="user", type="string", length=255)
     */
    private $user;


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
     * Set monthdayrepeat
     *
     * @param string $monthdayrepeat
     */
    public function setMonthdayrepeat($monthdayrepeat)
    {
        $this->monthdayrepeat = $monthdayrepeat;
    }

    /**
     * Get monthdayrepeat
     *
     * @return string 
     */
    public function getMonthdayrepeat()
    {
        return $this->monthdayrepeat;
    }

    /**
     * Set weekdayrepeat
     *
     * @param string $weekdayrepeat
     */
    public function setWeekdayrepeat($weekdayrepeat)
    {
        $this->weekdayrepeat = $weekdayrepeat;
    }

    /**
     * Get weekdayrepeat
     *
     * @return string 
     */
    public function getWeekdayrepeat()
    {
        return $this->weekdayrepeat;
    }

    /**
     * Set monthrepeat
     *
     * @param string $monthrepeat
     */
    public function setMonthrepeat($monthrepeat)
    {
        $this->monthrepeat = $monthrepeat;
    }

    /**
     * Get monthrepeat
     *
     * @return string 
     */
    public function getMonthrepeat()
    {
        return $this->monthrepeat;
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
}