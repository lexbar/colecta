<?php

namespace Colecta\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ActivityBundle\Entity\Place
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Place extends \Colecta\ItemBundle\Entity\Item
{

    /**
     * @var text $text
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
     * @var float $latitude
     *
     * @ORM\Column(name="latitude", type="float")
     */
    protected $latitude;

    /**
     * @var float $longitude
     *
     * @ORM\Column(name="longitude", type="float")
     */
    protected $longitude;
    
    //Heritage
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var boolean
     */
    protected $part;

    /**
     * @var string
     */
    protected $summary;

    /**
     * @var string
     */
    protected $tagwords;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var boolean
     */
    protected $allowComments;

    /**
     * @var boolean
     */
    protected $draft;

    /**
     * @var \Colecta\ActivityBundle\Entity\Category
     */
    protected $category;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $relatedto;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $relatedfrom;

    /**
     * @var \Colecta\UserBundle\Entity\User
     */
    protected $author;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $editors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $likers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $comments;
    
    public function getType()
    {
        return 'Activity/Place';
    }
    
    public function getViewPath()
    {
        return 'ColectaPlaceView';
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Place
     */
    public function setText($text)
    {
        $text = str_replace(array("<br>","<br />"),"\n",str_replace("\n",'',$text));
        $text = preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "\n", $text));
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
     * Set latitude
     *
     * @param float $latitude
     * @return Place
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    
        return $this;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return Place
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    
        return $this;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
}