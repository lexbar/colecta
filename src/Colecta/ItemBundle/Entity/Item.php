<?php

namespace Colecta\ItemBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\ItemBundle\Entity\Item
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"Item/Item" = "Item", "Item/Post" = "Post", "Colective/Poll" = "Colecta\ColectiveBundle\Entity\Poll", "Activity/Route" = "Colecta\ActivityBundle\Entity\Route", "Activity/Place" = "Colecta\ActivityBundle\Entity\Place", "Files/File" = "Colecta\FilesBundle\Entity\File", "Colective/Contest" = "Colecta\ColectiveBundle\Entity\Contest", "Activity/Event" = "Colecta\ActivityBundle\Entity\Event", "Files/Folder" = "Colecta\FilesBundle\Entity\Folder"})
 */
abstract class Item
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
    * @ORM\ManyToOne(targetEntity="Category")
    * @ORM\JoinColumn(name="category_id", referencedColumnName="id") 
    */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Relation", mappedBy="itemto")
     */
    private $relatedto;

    /**
     * @ORM\OneToMany(targetEntity="Relation", mappedBy="itemfrom")
     */
    private $relatedfrom;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id") 
    */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="Colecta\UserBundle\Entity\User", mappedBy="editableItems", cascade={"persist"})
     */
    private $editors;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="item")
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
     * Set editors
     *
     * @param string $editors
     */
    public function setEditors($editors)
    {
        $this->editors = $editors;
    }

    /**
     * Get editors
     *
     * @return string 
     */
    public function getEditors()
    {
        return $this->editors;
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
    public function __construct()
    {
        $this->relatedto = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relatedfrom = new \Doctrine\Common\Collections\ArrayCollection();
        $this->editors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add relatedto
     *
     * @param Colecta\ItemBundle\Entity\Relation $relatedto
     */
    public function addRelation(\Colecta\ItemBundle\Entity\Relation $relatedto)
    {
        $this->relatedto[] = $relatedto;
    }

    /**
     * Add editors
     *
     * @param Colecta\UserBundle\Entity\User $editors
     */
    public function addUser(\Colecta\UserBundle\Entity\User $editors)
    {
        $this->editors[] = $editors;
    }

    /**
     * Add comments
     *
     * @param Colecta\ItemBundle\Entity\Comment $comments
     */
    public function addComment(\Colecta\ItemBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;
    }
    
    public function generateSlug($string = false, $separator = '-') {
        
        if(!$string) 
        {
            $string = $this->getName();
        }
        
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $string); 
        $slug = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $slug); 
        $slug = strtolower(trim($slug, $separator)); 
        $slug = preg_replace("/[\/_|+ -]+/", $separator, $slug);
        
        return $slug;
    }
}