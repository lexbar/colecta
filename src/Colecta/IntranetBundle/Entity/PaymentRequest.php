<?php

namespace Colecta\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\IntranetBundle\Entity\PaymentRequest
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PaymentRequest
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
     * @var boolean $paid
     *
     * @ORM\Column(name="paid", type="boolean")
     */
    private $paid;

    /**
     * @var datetime $paymentdate
     *
     * @ORM\Column(name="paymentdate", type="datetime")
     */
    private $paymentdate;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id") 
    */
    private $user;

    /**
    * @ORM\ManyToOne(targetEntity="Payment")
    * @ORM\JoinColumn(name="payment_id", referencedColumnName="id") 
    */
    private $payment;


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
     * Set paid
     *
     * @param boolean $paid
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
    }

    /**
     * Get paid
     *
     * @return boolean 
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set paymentdate
     *
     * @param datetime $paymentdate
     */
    public function setPaymentdate($paymentdate)
    {
        $this->paymentdate = $paymentdate;
    }

    /**
     * Get paymentdate
     *
     * @return datetime 
     */
    public function getPaymentdate()
    {
        return $this->paymentdate;
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
     * Set payment
     *
     * @param string $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get payment
     *
     * @return string 
     */
    public function getPayment()
    {
        return $this->payment;
    }
}