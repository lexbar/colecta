<?php

namespace Colecta\ItemBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ItemBundle\Entity\Post
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Post extends Item
{

    /**
     * @var text $text
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

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
    
    public function getType()
    {
        return 'Item/Post';
    }
    
    public function __toString()
    {
        return $this->name;
    }
}