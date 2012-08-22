<?php

namespace Colecta\ColectiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ColectiveBundle\Entity\PollOption
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PollOption
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
     * @var string $text
     *
     * @ORM\Column(name="text", type="string", length=255)
     */
    private $text;

    /**
    * @ORM\ManyToOne(targetEntity="Poll")
    * @ORM\JoinColumn(name="poll_id", referencedColumnName="id") 
    */
    private $poll;

    /**
     * @ORM\ManyToMany(targetEntity="Colecta\UserBundle\Entity\User")
     * @ORM\JoinTable(name="votes",
     * joinColumns={@ORM\JoinColumn(name="polloption_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    private $votes;


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
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set poll
     *
     * @param string $poll
     */
    public function setPoll($poll)
    {
        $this->poll = $poll;
    }

    /**
     * Get poll
     *
     * @return string 
     */
    public function getPoll()
    {
        return $this->poll;
    }
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add votes
     *
     * @param Colecta\UserBundle\Entity\User $votes
     */
    public function addUser(\Colecta\UserBundle\Entity\User $votes)
    {
        $this->votes[] = $votes;
    }

    /**
     * Get votes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getVotes()
    {
        return $this->votes;
    }
}