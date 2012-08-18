<?php

namespace Colecta\ColectiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ColectiveBundle\Entity\Contest
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Contest extends \Colecta\ItemBundle\Entity\Item
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
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var datetime $iniDate
     *
     * @ORM\Column(name="iniDate", type="datetime")
     */
    private $iniDate;

    /**
     * @var datetime $endDate
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @var string $itemTypes
     *
     * @ORM\Column(name="itemTypes", type="string", length=255)
     */
    private $itemTypes;

    /**
     * @ORM\OneToMany(targetEntity="ContestWinner", mappedBy="contest")
     */
    private $winners;


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
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set iniDate
     *
     * @param datetime $iniDate
     */
    public function setIniDate($iniDate)
    {
        $this->iniDate = $iniDate;
    }

    /**
     * Get iniDate
     *
     * @return datetime 
     */
    public function getIniDate()
    {
        return $this->iniDate;
    }

    /**
     * Set endDate
     *
     * @param datetime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return datetime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set itemTypes
     *
     * @param string $itemTypes
     */
    public function setItemTypes($itemTypes)
    {
        $this->itemTypes = $itemTypes;
    }

    /**
     * Get itemTypes
     *
     * @return string 
     */
    public function getItemTypes()
    {
        return $this->itemTypes;
    }

    /**
     * Set winners
     *
     * @param string $winners
     */
    public function setWinners($winners)
    {
        $this->winners = $winners;
    }

    /**
     * Get winners
     *
     * @return string 
     */
    public function getWinners()
    {
        return $this->winners;
    }
    public function __construct()
    {
        $this->winners = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add winners
     *
     * @param Colecta\ColectiveBundle\Entity\ContestWinner $winners
     */
    public function addContestWinner(\Colecta\ColectiveBundle\Entity\ContestWinner $winners)
    {
        $this->winners[] = $winners;
    }
    
    public function getType()
    {
        return 'Colective/Contest';
    }
}