<?php

namespace Colecta\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\UserBundle\Entity\Message
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Message
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @var text $text
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
    * @ORM\ManyToOne(targetEntity="Message")
    * @ORM\JoinColumn(name="responseto_id", referencedColumnName="id") 
    */
    protected $responseto;

    /**
    * @ORM\ManyToOne(targetEntity="User", inversedBy="sentMessages")
    * @ORM\JoinColumn(name="user_origin_id", referencedColumnName="id") 
    */
    protected $origin;

    /**
    * @ORM\ManyToOne(targetEntity="User", inversedBy="receivedMessages")
    * @ORM\JoinColumn(name="user_destination_id", referencedColumnName="id") 
    */
    protected $destination;

    /**
     * @var boolean $dismiss
     *
     * @ORM\Column(name="dismiss", type="boolean")
     */
    protected $dismiss;
    
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
     * Set text
     *
     * @param text $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return text 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set responseto
     *
     * @param string $responseto
     */
    public function setResponseto($responseto)
    {
        $this->responseto = $responseto;
    }

    /**
     * Get responseto
     *
     * @return string 
     */
    public function getResponseto()
    {
        return $this->responseto;
    }

    /**
     * Set origin
     *
     * @param string $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * Get origin
     *
     * @return string 
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set destination
     *
     * @param string $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * Get destination
     *
     * @return string 
     */
    public function getDestination()
    {
        return $this->destination;
    }
    
    /**
     * Set dismiss
     *
     * @param boolean $dismiss
     */
    public function setDismiss($dismiss)
    {
        $this->dismiss = $dismiss;
    }

    /**
     * Get dismiss
     *
     * @return boolean 
     */
    public function getDismiss()
    {
        return $this->dismiss;
    }
}
