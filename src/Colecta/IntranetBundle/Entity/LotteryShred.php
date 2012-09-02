<?php

namespace Colecta\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\IntranetBundle\Entity\LotteryShred
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class LotteryShred
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
     * @var integer $start
     *
     * @ORM\Column(name="start", type="integer")
     */
    private $start;

    /**
     * @var integer $end
     *
     * @ORM\Column(name="end", type="integer")
     */
    private $end;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var boolean $paid
     *
     * @ORM\Column(name="paid", type="boolean")
     */
    private $paid;

    /**
     * @var boolean $returned
     *
     * @ORM\Column(name="returned", type="boolean")
     */
    private $returned;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id") 
    */
    private $user;

    /**
    * @ORM\ManyToOne(targetEntity="LotteryCampaign")
    * @ORM\JoinColumn(name="lotteryCampaign_id", referencedColumnName="id")
    */
    private $lotteryCampaign;


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
     * Set start
     *
     * @param integer $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * Get start
     *
     * @return integer 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param integer $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Get end
     *
     * @return integer 
     */
    public function getEnd()
    {
        return $this->end;
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
     * Set returned
     *
     * @param boolean $returned
     */
    public function setReturned($returned)
    {
        $this->returned = $returned;
    }

    /**
     * Get returned
     *
     * @return boolean 
     */
    public function getReturned()
    {
        return $this->returned;
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
     * Set lotteryCampaign
     *
     * @param string $lotteryCampaign
     */
    public function setLotteryCampaign($lotteryCampaign)
    {
        $this->lotteryCampaign = $lotteryCampaign;
    }

    /**
     * Get lotteryCampaign
     *
     * @return string 
     */
    public function getLotteryCampaign()
    {
        return $this->lotteryCampaign;
    }
}