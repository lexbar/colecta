<?php

namespace Colecta\ColectiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ColectiveBundle\Entity\Poll
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Poll extends \Colecta\ItemBundle\Entity\Item
{
    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @var datetime $endDate
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    protected $endDate;

    /**
     * @ORM\OneToMany(targetEntity="PollOption", mappedBy="poll")
     */
    protected $options;
    
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
     * Set options
     *
     * @param string $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Get options
     *
     * @return string 
     */
    public function getOptions()
    {
        return $this->options;
    }
    public function __construct()
    {
        $this->options = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add options
     *
     * @param Colecta\ColectiveBundle\Entity\PollOption $options
     */
    public function addPollOption(\Colecta\ColectiveBundle\Entity\PollOption $options)
    {
        $this->options[] = $options;
    }
    
    public function getType()
    {
        return 'Colective/Poll';
    }
}