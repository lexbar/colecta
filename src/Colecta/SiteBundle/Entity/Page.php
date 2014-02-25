<?php

namespace Colecta\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Page
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Page
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var boolean
     *
     * @ORM\Column(name="draft", type="boolean")
     */
    protected $draft;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\User")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id") 
    */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sidebarShow", type="boolean")
     */
    protected $sidebarShow;
    
    /**
     * @var smallint
     *
     * @ORM\Column(name="sidebarOrder", type="smallint")
     */
    protected $sidebarOrder;
    
    /**
    * @ORM\ManyToOne(targetEntity="Colecta\UserBundle\Entity\Role")
    * @ORM\JoinColumn(name="role_id", referencedColumnName="id") 
    */
    protected $roleTarget;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="contact", type="boolean")
     */
    protected $contact;
    
    /**
     * @var array
     *
     * @ORM\Column(name="contactData", type="array")
     */
    protected $contactData;

    /**
     * @var string
     *
     * @ORM\Column(name="context", type="string", length=20)
     */
    protected $context; //extranet, intranet, backend, all
    
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
     * @return Page
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
     * @return Page
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
     * Set draft
     *
     * @param boolean $draft
     * @return Page
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
     * Set date
     *
     * @param \DateTime $date
     * @return Page
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
     * Set text
     *
     * @param string $text
     * @return Page
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
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
     * Set sidebarShow
     *
     * @param boolean $sidebarShow
     * @return Page
     */
    public function setSidebarShow($sidebarShow)
    {
        $this->sidebarShow = $sidebarShow;
    
        return $this;
    }

    /**
     * Get sidebarShow
     *
     * @return boolean 
     */
    public function getSidebarShow()
    {
        return $this->sidebarShow;
    }

    /**
     * Set sidebarOrder
     *
     * @param integer $sidebarOrder
     * @return Page
     */
    public function setSidebarOrder($sidebarOrder)
    {
        $this->sidebarOrder = $sidebarOrder;
    
        return $this;
    }

    /**
     * Get sidebarOrder
     *
     * @return integer 
     */
    public function getSidebarOrder()
    {
        return $this->sidebarOrder;
    }

    /**
     * Set contact
     *
     * @param boolean $contact
     * @return Page
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    
        return $this;
    }

    /**
     * Get contact
     *
     * @return boolean 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set contactData
     *
     * @param array $contactData
     * @return Page
     */
    public function setContactData($contactData)
    {
        $this->contactData = $contactData;
    
        return $this;
    }

    /**
     * Get contactData
     *
     * @return array 
     */
    public function getContactData()
    {
        return $this->contactData;
    }

    /**
     * Set author
     *
     * @param \Colecta\UserBundle\Entity\User $author
     * @return Page
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
     * Set roleTarget
     *
     * @param \Colecta\SiteBundle\Entity\Role $roleTarget
     * @return Page
     */
    public function setRoleTarget(\Colecta\SiteBundle\Entity\Role $roleTarget = null)
    {
        $this->roleTarget = $roleTarget;
    
        return $this;
    }

    /**
     * Get roleTarget
     *
     * @return \Colecta\SiteBundle\Entity\Role 
     */
    public function getRoleTarget()
    {
        return $this->roleTarget;
    }

    /**
     * Set context
     *
     * @param string $context
     * @return Page
     */
    public function setContext($context)
    {
        $this->context = $context;
    
        return $this;
    }

    /**
     * Get context
     *
     * @return string 
     */
    public function getContext()
    {
        return $this->context;
    }
}