<?php

namespace Colecta\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\IntranetBundle\Entity\Payment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Payment
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
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id") 
    */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="PaymentRequest", mappedBy="payment_id")
     */
    private $paymentRequests;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\ActivityBundle\Entity\Event")
    * @ORM\JoinColumn(name="event_id", referencedColumnName="id") 
    */
    private $event;


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
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set paymentRequests
     *
     * @param string $paymentRequests
     */
    public function setPaymentRequests($paymentRequests)
    {
        $this->paymentRequests = $paymentRequests;
    }

    /**
     * Get paymentRequests
     *
     * @return string 
     */
    public function getPaymentRequests()
    {
        return $this->paymentRequests;
    }

    /**
     * Set event
     *
     * @param string $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return string 
     */
    public function getEvent()
    {
        return $this->event;
    }
    public function __construct()
    {
        $this->paymentRequests = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add paymentRequests
     *
     * @param Colecta\IntranetBundle\Entity\PaymentRequest $paymentRequests
     */
    public function addPaymentRequest(\Colecta\IntranetBundle\Entity\PaymentRequest $paymentRequests)
    {
        $this->paymentRequests[] = $paymentRequests;
    }
}