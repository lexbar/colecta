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
    protected $text;
    
    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=255)
     */
    protected $linkImage;
    
    /**
     * @var string $linkURL
     *
     * @ORM\Column(name="linkURL", type="string", length=255)
     */
    protected $linkURL;
    
    /**
     * @var string $linkTitle
     *
     * @ORM\Column(name="linkTitle", type="string", length=255)
     */
    protected $linkTitle;
    
    /**
     * @var string $linkExcerpt
     *
     * @ORM\Column(name="linkExcerpt", type="string", length=255)
     */
    protected $linkExcerpt;


    /**
     * Set text
     *
     * @param text $text
     */
    public function setText($text)
    {
        $text = preg_replace("/(\n|\r\n|\r)+$/ise","",$text);
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
     * Set linkImage
     *
     * @param string $linkImage
     * @return Post
     */
    public function setLinkImage($linkImage)
    {
        $this->linkImage = $linkImage;
    
        return $this;
    }

    /**
     * Get linkImage
     *
     * @return string 
     */
    public function getLinkImage()
    {
        return $this->linkImage;
    }

    /**
     * Set linkURL
     *
     * @param string $linkURL
     * @return Post
     */
    public function setLinkURL($linkURL)
    {
        $this->linkURL = $linkURL;
    
        return $this;
    }

    /**
     * Get linkURL
     *
     * @return string 
     */
    public function getLinkURL()
    {
        return $this->linkURL;
    }

    /**
     * Set linkTitle
     *
     * @param string $linkTitle
     * @return Post
     */
    public function setLinkTitle($linkTitle)
    {
        $this->linkTitle = $linkTitle;
    
        return $this;
    }

    /**
     * Get linkTitle
     *
     * @return string 
     */
    public function getLinkTitle()
    {
        return $this->linkTitle;
    }

    /**
     * Set linkExcerpt
     *
     * @param string $linkExcerpt
     * @return Post
     */
    public function setLinkExcerpt($linkExcerpt)
    {
        $this->linkExcerpt = $linkExcerpt;
    
        return $this;
    }

    /**
     * Get linkExcerpt
     *
     * @return string 
     */
    public function getLinkExcerpt()
    {
        return $this->linkExcerpt;
    }
    
    public function getType()
    {
        return 'Item/Post';
    }
    
    public function getViewPath()
    {
        return 'ColectaPostView';
    }
    
    public function __toString()
    {
        return $this->name;
    }
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
     * @var \Colecta\ItemBundle\Entity\Category
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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->relatedto = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relatedfrom = new \Doctrine\Common\Collections\ArrayCollection();
        $this->editors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->likers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * @return Post
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
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
     * Set slug
     *
     * @param string $slug
     * @return Post
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set part
     *
     * @param boolean $part
     * @return Post
     */
    public function setPart($part)
    {
        $this->part = $part;
    
        return $this;
    }

    /**
     * Get part
     *
     * @return boolean 
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return Post
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    
        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set tagwords
     *
     * @param string $tagwords
     * @return Post
     */
    public function setTagwords($tagwords)
    {
        $this->tagwords = $tagwords;
    
        return $this;
    }

    /**
     * Get tagwords
     *
     * @return string 
     */
    public function getTagwords()
    {
        return $this->tagwords;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Post
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set allowComments
     *
     * @param boolean $allowComments
     * @return Post
     */
    public function setAllowComments($allowComments)
    {
        $this->allowComments = $allowComments;
    
        return $this;
    }

    /**
     * Get allowComments
     *
     * @return boolean 
     */
    public function getAllowComments()
    {
        return $this->allowComments;
    }

    /**
     * Set draft
     *
     * @param boolean $draft
     * @return Post
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;
    
        return $this;
    }

    /**
     * Get draft
     *
     * @return boolean 
     */
    public function getDraft()
    {
        return $this->draft;
    }

    /**
     * Set category
     *
     * @param \Colecta\ItemBundle\Entity\Category $category
     * @return Post
     */
    public function setCategory(\Colecta\ItemBundle\Entity\Category $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return \Colecta\ItemBundle\Entity\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add relatedto
     *
     * @param \Colecta\ItemBundle\Entity\Relation $relatedto
     * @return Post
     */
    public function addRelatedto(\Colecta\ItemBundle\Entity\Relation $relatedto)
    {
        $this->relatedto[] = $relatedto;
    
        return $this;
    }

    /**
     * Remove relatedto
     *
     * @param \Colecta\ItemBundle\Entity\Relation $relatedto
     */
    public function removeRelatedto(\Colecta\ItemBundle\Entity\Relation $relatedto)
    {
        $this->relatedto->removeElement($relatedto);
    }

    /**
     * Get relatedto
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedto()
    {
        return $this->relatedto;
    }

    /**
     * Add relatedfrom
     *
     * @param \Colecta\ItemBundle\Entity\Relation $relatedfrom
     * @return Post
     */
    public function addRelatedfrom(\Colecta\ItemBundle\Entity\Relation $relatedfrom)
    {
        $this->relatedfrom[] = $relatedfrom;
    
        return $this;
    }

    /**
     * Remove relatedfrom
     *
     * @param \Colecta\ItemBundle\Entity\Relation $relatedfrom
     */
    public function removeRelatedfrom(\Colecta\ItemBundle\Entity\Relation $relatedfrom)
    {
        $this->relatedfrom->removeElement($relatedfrom);
    }

    /**
     * Get relatedfrom
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedfrom()
    {
        return $this->relatedfrom;
    }

    /**
     * Set author
     *
     * @param \Colecta\UserBundle\Entity\User $author
     * @return Post
     */
    public function setAuthor(\Colecta\UserBundle\Entity\User $author = null)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * Get author
     *
     * @return \Colecta\UserBundle\Entity\User 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Add editors
     *
     * @param \Colecta\UserBundle\Entity\User $editors
     * @return Post
     */
    public function addEditor(\Colecta\UserBundle\Entity\User $editors)
    {
        $this->editors[] = $editors;
    
        return $this;
    }

    /**
     * Remove editors
     *
     * @param \Colecta\UserBundle\Entity\User $editors
     */
    public function removeEditor(\Colecta\UserBundle\Entity\User $editors)
    {
        $this->editors->removeElement($editors);
    }

    /**
     * Get editors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEditors()
    {
        return $this->editors;
    }

    /**
     * Add likers
     *
     * @param \Colecta\UserBundle\Entity\User $likers
     * @return Post
     */
    public function addLiker(\Colecta\UserBundle\Entity\User $likers)
    {
        $this->likers[] = $likers;
    
        return $this;
    }

    /**
     * Remove likers
     *
     * @param \Colecta\UserBundle\Entity\User $likers
     */
    public function removeLiker(\Colecta\UserBundle\Entity\User $likers)
    {
        $this->likers->removeElement($likers);
    }

    /**
     * Get likers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLikers()
    {
        return $this->likers;
    }

    /**
     * Add comments
     *
     * @param \Colecta\ItemBundle\Entity\Comment $comments
     * @return Post
     */
    public function addComment(\Colecta\ItemBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;
    
        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Colecta\ItemBundle\Entity\Comment $comments
     */
    public function removeComment(\Colecta\ItemBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }
    /**
     * @var \DateTime
     */
    protected $lastInteraction;


    /**
     * Set lastInteraction
     *
     * @param \DateTime $lastInteraction
     * @return Post
     */
    public function setLastInteraction($lastInteraction)
    {
        $this->lastInteraction = $lastInteraction;
    
        return $this;
    }

    /**
     * Get lastInteraction
     *
     * @return \DateTime 
     */
    public function getLastInteraction()
    {
        return $this->lastInteraction;
    }
}