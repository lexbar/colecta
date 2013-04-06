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
 * @ORM\DiscriminatorMap({"Item/Item" = "Item", "Item/Post" = "Post", "Colective/Poll" = "Colecta\ColectiveBundle\Entity\Poll", "Activity/Route" = "Colecta\ActivityBundle\Entity\Route", "Activity/Place" = "Colecta\ActivityBundle\Entity\Place", "Files/File" = "Colecta\FilesBundle\Entity\File", "Colective/Contest" = "Colecta\ColectiveBundle\Entity\Contest", "Activity/Event" = "Colecta\ActivityBundle\Entity\Event", "Files/Folder" = "Colecta\FilesBundle\Entity\Folder", "Activity/CompoundEvent" = "Colecta\ActivityBundle\Entity\CompoundEvent"})
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
    protected $id;
    
    protected $type;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;
    
    /**
     * @var boolean $part
     *
     * @ORM\Column(name="part", type="boolean")
     */
    protected $part;

    /**
     * @var string $summary
     *
     * @ORM\Column(name="summary", type="string", length=255)
     */
    protected $summary;

    /**
     * @var string $tagwords
     *
     * @ORM\Column(name="tagwords", type="string", length=255)
     */
    protected $tagwords;

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @var boolean $allowComments
     *
     * @ORM\Column(name="allowComments", type="boolean")
     */
    protected $allowComments;

    /**
     * @var boolean $draft
     *
     * @ORM\Column(name="draft", type="boolean")
     */
    protected $draft;

    /**
    * @ORM\ManyToOne(targetEntity="Category")
    * @ORM\JoinColumn(name="category_id", referencedColumnName="id") 
    */
    protected $category;

    /**
     * @ORM\OneToMany(targetEntity="Relation", mappedBy="itemto")
     */
    protected $relatedto;

    /**
     * @ORM\OneToMany(targetEntity="Relation", mappedBy="itemfrom")
     */
    protected $relatedfrom;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id") 
    */
    protected $author;

    /**
     * @ORM\ManyToMany(targetEntity="Colecta\UserBundle\Entity\User", mappedBy="editableItems", cascade={"persist"})
     */
    protected $editors;
    
    /**
     * @ORM\ManyToMany(targetEntity="Colecta\UserBundle\Entity\User", mappedBy="likedItems", cascade={"persist"})
     */
    protected $likers;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="item")
     */
    protected $comments;
    
    /**
     * Get related
     *
     * @return string 
     */
    public function getRelated()
    {
        $from = $this->getRelatedfrom();
        $to = $this->getRelatedto();
        
        $all = array();
        
        if(count($from))
        {
            foreach($from as $i)
            {
                $item = $i->getItemto();
                $index = intval($item->getDate()->format('U'));
                while(isset($all[$index])) {
                    $index++;
                }
                $all[$index] = $item;
            }
        }
        
        if(count($to))
        {
            foreach($to as $i)
            {
                $item = $i->getItemfrom();
                $index = intval($item->getDate()->format('U'));
                while(isset($all[$index])) {
                    $index++;
                }
                $all[$index] = $item;
            }
        }
        
        ksort($all);
        
        return array_values($all);
    }

     public function __construct()
    {
        $this->relatedto = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relatedfrom = new \Doctrine\Common\Collections\ArrayCollection();
        $this->editors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        
        //Default values
        $this->part = false;
        $this->allowComments = true;
        $this->draft = true;
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
    
    public function summarize($text)
    {
        //Fill the Summary field, first 255 characters
        if(strlen($text) > 255)
        {
            $summary = '';
            for($i = 0; $i < 255; $i++)
            {
                if($text[$i] == ' ')
                {
                    $summary = substr($text, 0, $i);
                }
            }
            
            //And now the tagwords if the text was longer than 255
            
            $words = explode(' ', str_replace(array(',',';','.',':','=','(',')','?','!'), ' ', $text));
            $summarywords = explode(' ', str_replace(array(',','.',':',';','=','(',')','?','!'), ' ', $summary));
            
            $tags = array();
            
            foreach($words as $w)
            {
                if(
                    !empty($w) //is not empty
                    && !in_array($w,$summarywords) //not already in the summary
                    && strlen($w) > 2 //at least 3 letters
                    )
                {
                    $tags[] = $w;
                }
            }
            
            $tagwords = substr(implode(' ',$tags), 0, 255);
        }
        else
        {
            $summary = $text;
            $tagwords = '';
        }
        
        
        $this->setSummary($summary);
        $this->setTagwords($tagwords);
    }
    
    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->setDate(new \DateTime('now'));
    }


    
    public function userLikes(\Colecta\UserBundle\Entity\User $user)
    {
        $likers = $this->getLikers();
        if(count($likers) < 1) return false;
        
        foreach($likers as $l) 
        {
            if($l == $user) return true;
        }
        
        return false;
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
}