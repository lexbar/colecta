<?php

namespace Colecta\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\BackendBundle\Entity\LotteryCampaign
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
     * @var string $lotteryShreds
     *
     * @ORM\Column(name="lotteryShreds", type="string", length=255)
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
}