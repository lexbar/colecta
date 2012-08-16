<?php

namespace Colecta\ItemBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ItemBundle\Entity\Item
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Item
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
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=25)
     */
    private $type;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var string $summary
     *
     * @ORM\Column(name="summary", type="string", length=255)
     */
    private $summary;

    /**
     * @var string $tagwords
     *
     * @ORM\Column(name="tagwords", type="string", length=255)
     */
    private $tagwords;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var boolean $allowComments
     *
     * @ORM\Column(name="allowComments", type="boolean")
     */
    private $allowComments;

    /**
     * @var boolean $draft
     *
     * @ORM\Column(name="draft", type="boolean")
     */
    private $draft;

    /**
     * @var string $category
     *
     * @ORM\Column(name="category", type="string", length=255)
     */
    private $category;

    /**
     * @var string $relatedto
     *
     * @ORM\Column(name="relatedto", type="string", length=255)
     */
    private $relatedto;

    /**
     * @var string $relatedfrom
     *
     * @ORM\Column(name="relatedfrom", type="string", length=255)
     */
    private $relatedfrom;

    /**
     * @var string $author
     *
     * @ORM\Column(name="author", type="string", length=255)
     */
    private $author;

    /**
     * @var string $canedit
     *
     * @ORM\Column(name="canedit", type="string", length=255)
     */
    private $canedit;

    /**
     * @var string $comments
     *
     * @ORM\Column(name="comments", type="string", length=255)
     */
    private $comments;


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
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
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
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
     * Set summary
     *
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
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
     */
    public function setTagwords($tagwords)
    {
        $this->tagwords = $tagwords;
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
     * Set allowComments
     *
     * @param boolean $allowComments
     */
    public function setAllowComments($allowComments)
    {
        $this->allowComments = $allowComments;
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
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;
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
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return string 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set relatedto
     *
     * @param string $relatedto
     */
    public function setRelatedto($relatedto)
    {
        $this->relatedto = $relatedto;
    }

    /**
     * Get relatedto
     *
     * @return string 
     */
    public function getRelatedto()
    {
        return $this->relatedto;
    }

    /**
     * Set relatedfrom
     *
     * @param string $relatedfrom
     */
    public function setRelatedfrom($relatedfrom)
    {
        $this->relatedfrom = $relatedfrom;
    }

    /**
     * Get relatedfrom
     *
     * @return string 
     */
    public function getRelatedfrom()
    {
        return $this->relatedfrom;
    }

    /**
     * Set author
     *
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set canedit
     *
     * @param string $canedit
     */
    public function setCanedit($canedit)
    {
        $this->canedit = $canedit;
    }

    /**
     * Get canedit
     *
     * @return string 
     */
    public function getCanedit()
    {
        return $this->canedit;
    }

    /**
     * Set comments
     *
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * Get comments
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->comments;
    }
}