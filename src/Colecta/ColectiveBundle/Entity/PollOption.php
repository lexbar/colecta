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
     * @var string $poll
     *
     * @ORM\Column(name="poll", type="string", length=255)
     */
    private $poll;

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