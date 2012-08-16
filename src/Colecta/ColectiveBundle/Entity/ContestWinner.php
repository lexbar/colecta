<?php

namespace Colecta\ColectiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ColectiveBundle\Entity\ContestWinner
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ContestWinner
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
     * @var smallint $position
     *
     * @ORM\Column(name="position", type="smallint")
     */
    private $position;

    /**
     * @var string $text
     *
     * @ORM\Column(name="text", type="string", length=255)
     */
    private $text;

    /**
     * @var string $contest
     *
     * @ORM\Column(name="contest", type="string", length=255)
     */
    private $contest;

    /**
     * @var string $item
     *
     * @ORM\Column(name="item", type="string", length=255)
     */
    private $item;

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
     * Set position
     *
     * @param smallint $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return smallint 
     */
    public function getPosition()
    {
        return $this->position;
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
     * Set contest
     *
     * @param string $contest
     */
    public function setContest($contest)
    {
        $this->contest = $contest;
    }

    /**
     * Get contest
     *
     * @return string 
     */
    public function getContest()
    {
        return $this->contest;
    }

    /**
     * Set item
     *
     * @param string $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * Get item
     *
     * @return string 
     */
    public function getItem()
    {
        return $this->item;
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