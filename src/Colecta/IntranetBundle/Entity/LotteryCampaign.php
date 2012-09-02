<?php

namespace Colecta\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\IntranetBundle\Entity\LotteryCampaign
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class LotteryCampaign
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
     * @ORM\OneToMany(targetEntity="LotteryShred", mappedBy="lotteryCampaign_id")
     */
    private $lotteryShreds;


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
     * Set lotteryShreds
     *
     * @param string $lotteryShreds
     */
    public function setLotteryShreds($lotteryShreds)
    {
        $this->lotteryShreds = $lotteryShreds;
    }

    /**
     * Get lotteryShreds
     *
     * @return string 
     */
    public function getLotteryShreds()
    {
        return $this->lotteryShreds;
    }
    public function __construct()
    {
        $this->lotteryShreds = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add lotteryShreds
     *
     * @param Colecta\IntranetBundle\Entity\LotteryShred $lotteryShreds
     */
    public function addLotteryShred(\Colecta\IntranetBundle\Entity\LotteryShred $lotteryShreds)
    {
        $this->lotteryShreds[] = $lotteryShreds;
    }
}