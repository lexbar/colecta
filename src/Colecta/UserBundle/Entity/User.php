<?php

namespace Colecta\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Colecta\UserBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    protected $name;

    /**
     * @var string $mail
     *
     * @ORM\Column(name="mail", type="string", length=180)
     */
    protected $mail;

    /**
     * @var string $pass
     *
     * @ORM\Column(name="pass", type="string", length=255)
     */
    protected $pass;

    /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=255)
     */
    protected $salt;
    
    /**
     * @Assert\Image(maxSize="6000000")
     */
    protected $file;

    /**
     * @var string $avatar
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    protected $avatar;

    /**
     * @var datetime $registered
     *
     * @ORM\Column(name="registered", type="datetime")
     */
    protected $registered;

    /**
     * @var datetime $lastAccess
     *
     * @ORM\Column(name="lastAccess", type="datetime")
     */
    protected $lastAccess;

    /**
    * @ORM\ManyToOne(targetEntity="Role")
    * @ORM\JoinColumn(name="role_id", referencedColumnName="id") 
    */
    protected $role;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="origin")
     */
    protected $sentMessages;

    /**
     * @ORM\OneToMany(targetEntity="Colecta\ItemBundle\Entity\Relation", mappedBy="destination")
     */
    protected $receivedMessages;

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="user")
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(targetEntity="Colecta\ItemBundle\Entity\Relation", mappedBy="user")
     */
    protected $relations;

    /**
     * @ORM\OneToMany(targetEntity="Colecta\ItemBundle\Entity\Item", mappedBy="user")
     */
    protected $items;

    /**
     * @ORM\ManyToMany(targetEntity="Colecta\ItemBundle\Entity\Item", inversedBy="editors", cascade={"persist"})
     * @ORM\JoinTable(name="editableItems",
     * joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")}
     * )
     */
    protected $editableItems;
    
    /**
     * @ORM\ManyToMany(targetEntity="Colecta\ItemBundle\Entity\Item", inversedBy="likers", cascade={"persist"})
     * @ORM\JoinTable(name="likedItems",
     * joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")}
     * )
     */
    protected $likedItems;

    /**
     * @ORM\OneToMany(targetEntity="Colecta\ItemBundle\Entity\Comment", mappedBy="user")
     */    
     protected $comments;
     
     /**
     * @ORM\OneToMany(targetEntity="Colecta\ActivityBundle\Entity\EventAssistance", mappedBy="user")
     */    
     protected $assistances;


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
     * Set mail
     *
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set pass
     *
     * @param string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * Get pass
     *
     * @return string 
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set registered
     *
     * @param datetime $registered
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;
    }

    /**
     * Get registered
     *
     * @return datetime 
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * Set lastAccess
     *
     * @param datetime $lastAccess
     */
    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;
    }

    /**
     * Get lastAccess
     *
     * @return datetime 
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * Set role
     *
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set sentMessages
     *
     * @param string $sentMessages
     */
    public function setSentMessages($sentMessages)
    {
        $this->sentMessages = $sentMessages;
    }

    /**
     * Get sentMessages
     *
     * @return string 
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    /**
     * Set receivedMessages
     *
     * @param string $receivedMessages
     */
    public function setReceivedMessages($receivedMessages)
    {
        $this->receivedMessages = $receivedMessages;
    }

    /**
     * Get receivedMessages
     *
     * @return string 
     */
    public function getReceivedMessages()
    {
        return $this->receivedMessages;
    }

    /**
     * Set notifications
     *
     * @param string $notifications
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Get notifications
     *
     * @return string 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set relations
     *
     * @param string $relations
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
    }

    /**
     * Get relations
     *
     * @return string 
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Set items
     *
     * @param string $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * Get items
     *
     * @return string 
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set editableItems
     *
     * @param string $editableItems
     */
    public function setEditableItems($editableItems)
    {
        $this->editableItems = $editableItems;
    }

    /**
     * Get editableItems
     *
     * @return string 
     */
    public function getEditableItems()
    {
        return $this->editableItems;
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
        $this->sentMessages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->receivedMessages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->editableItems = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add sentMessages
     *
     * @param Colecta\UserBundle\Entity\Message $sentMessages
     */
    public function addMessage(\Colecta\UserBundle\Entity\Message $sentMessages)
    {
        $this->sentMessages[] = $sentMessages;
    }

    /**
     * Add receivedMessages
     *
     * @param Colecta\ItemBundle\Entity\Relation $receivedMessages
     */
    public function addRelation(\Colecta\ItemBundle\Entity\Relation $receivedMessages)
    {
        $this->receivedMessages[] = $receivedMessages;
    }

    /**
     * Add items
     *
     * @param Colecta\ItemBundle\Entity\Item $items
     */
    public function addItem(\Colecta\ItemBundle\Entity\Item $items)
    {
        $this->items[] = $items;
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

    /**
     * Add notifications
     *
     * @param Colecta\UserBundle\Entity\Notification $notifications
     */
    public function addNotification(\Colecta\UserBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;
    }
    
    public function equals(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        return $this->getMail() == $user->getMail();
    }
    
    public function eraseCredentials()
    {
    
    }
    
    public function getRoles()
    {
        return array('ROLE_USER');
    }
    
    public function getUsername()
    {
        return $this->getMail();
    }
    
    public function getPassword()
    {
        return $this->getPass();
    }
    
    // File upload
    
    /**
     * Virtual getter that returns logo web path
     * @return string
     */
    public function getAvatarPath() 
    {
        return $this->getAvatarWebPath();
    }
    
    protected function getUploadDir() 
    {
        return '/uploads/avatars';
    }
    
    public function getFile() 
    {
        return $this->file;
    }
    
    public function setFile($file) 
    {
        $this->file = $file;
    }
    
    public function getAvatar() 
    {
        return $this->avatar;
    }
    
    public function setAvatar($avatar) 
    {
        $this->avatar = $avatar;
    }
    
    public function getAvatarAbsolutePath() 
    {
        return null === $this->avatar ? null : $this->getUploadRootDir() . '/' . $this->avatar;
    }
    
    public function getAvatarWebPath() 
    {
        return null === $this->avatar ? null : $this->getUploadDir() . '/' . $this->avatar;
    }
    
    protected function getUploadRootDir() 
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web' . $this->getUploadDir();
    }
    
    public function upload() 
    {
        // the file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }
        
        $hashName = sha1($this->file->getClientOriginalName() . $this->getId() . mt_rand(0, 99999));
        
        $this->avatar = $hashName . '.' . $this->file->guessExtension();
        
        $this->file->move($this->getUploadRootDir(), $this->getAvatar());
        
        unset($this->file);
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAvatarAbsolutePath()) {
            unlink($file);
        }
    }
    
    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->setRegistered(new \DateTime('now'));
        $this->setLastAccess(new \DateTime('now'));
    }

    /**
     * Add assistances
     *
     * @param Colecta\ActivityBundle\Entity\EventAssistance $assistances
     */
    public function addEventAssistance(\Colecta\ActivityBundle\Entity\EventAssistance $assistances)
    {
        $this->assistances[] = $assistances;
    }

    /**
     * Get assistances
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAssistances()
    {
        return $this->assistances;
    }

    /**
     * Add sentMessages
     *
     * @param \Colecta\UserBundle\Entity\Message $sentMessages
     * @return User
     */
    public function addSentMessage(\Colecta\UserBundle\Entity\Message $sentMessages)
    {
        $this->sentMessages[] = $sentMessages;
    
        return $this;
    }

    /**
     * Remove sentMessages
     *
     * @param \Colecta\UserBundle\Entity\Message $sentMessages
     */
    public function removeSentMessage(\Colecta\UserBundle\Entity\Message $sentMessages)
    {
        $this->sentMessages->removeElement($sentMessages);
    }

    /**
     * Add receivedMessages
     *
     * @param \Colecta\ItemBundle\Entity\Relation $receivedMessages
     * @return User
     */
    public function addReceivedMessage(\Colecta\ItemBundle\Entity\Relation $receivedMessages)
    {
        $this->receivedMessages[] = $receivedMessages;
    
        return $this;
    }

    /**
     * Remove receivedMessages
     *
     * @param \Colecta\ItemBundle\Entity\Relation $receivedMessages
     */
    public function removeReceivedMessage(\Colecta\ItemBundle\Entity\Relation $receivedMessages)
    {
        $this->receivedMessages->removeElement($receivedMessages);
    }

    /**
     * Remove notifications
     *
     * @param \Colecta\UserBundle\Entity\Notification $notifications
     */
    public function removeNotification(\Colecta\UserBundle\Entity\Notification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Remove relations
     *
     * @param \Colecta\ItemBundle\Entity\Relation $relations
     */
    public function removeRelation(\Colecta\ItemBundle\Entity\Relation $relations)
    {
        $this->relations->removeElement($relations);
    }

    /**
     * Remove items
     *
     * @param \Colecta\ItemBundle\Entity\Item $items
     */
    public function removeItem(\Colecta\ItemBundle\Entity\Item $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Add editableItems
     *
     * @param \Colecta\ItemBundle\Entity\Item $editableItems
     * @return User
     */
    public function addEditableItem(\Colecta\ItemBundle\Entity\Item $editableItems)
    {
        $this->editableItems[] = $editableItems;
    
        return $this;
    }

    /**
     * Remove editableItems
     *
     * @param \Colecta\ItemBundle\Entity\Item $editableItems
     */
    public function removeEditableItem(\Colecta\ItemBundle\Entity\Item $editableItems)
    {
        $this->editableItems->removeElement($editableItems);
    }

    /**
     * Add likedItems
     *
     * @param \Colecta\ItemBundle\Entity\Item $likedItems
     * @return User
     */
    public function addLikedItem(\Colecta\ItemBundle\Entity\Item $likedItems)
    {
        $this->likedItems[] = $likedItems;
    
        return $this;
    }

    /**
     * Remove likedItems
     *
     * @param \Colecta\ItemBundle\Entity\Item $likedItems
     */
    public function removeLikedItem(\Colecta\ItemBundle\Entity\Item $likedItems)
    {
        $this->likedItems->removeElement($likedItems);
    }

    /**
     * Get likedItems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLikedItems()
    {
        return $this->likedItems;
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
     * Add assistances
     *
     * @param \Colecta\ActivityBundle\Entity\EventAssistance $assistances
     * @return User
     */
    public function addAssistance(\Colecta\ActivityBundle\Entity\EventAssistance $assistances)
    {
        $this->assistances[] = $assistances;
    
        return $this;
    }

    /**
     * Remove assistances
     *
     * @param \Colecta\ActivityBundle\Entity\EventAssistance $assistances
     */
    public function removeAssistance(\Colecta\ActivityBundle\Entity\EventAssistance $assistances)
    {
        $this->assistances->removeElement($assistances);
    }
    
    /**
     * @return string
     */
    public function serialize()
    {
      return serialize($this->id);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
      $this->id = unserialize($data);
    }
}