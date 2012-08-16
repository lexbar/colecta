<?php

namespace Colecta\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\UserBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User
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
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string $mail
     *
     * @ORM\Column(name="mail", type="string", length=180)
     */
    private $mail;

    /**
     * @var string $pass
     *
     * @ORM\Column(name="pass", type="string", length=255)
     */
    private $pass;

    /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=255)
     */
    private $salt;

    /**
     * @var string $avatar
     *
     * @ORM\Column(name="avatar", type="string", length=255)
     */
    private $avatar;

    /**
     * @var datetime $registered
     *
     * @ORM\Column(name="registered", type="datetime")
     */
    private $registered;

    /**
     * @var datetime $lastAccess
     *
     * @ORM\Column(name="lastAccess", type="datetime")
     */
    private $lastAccess;

    /**
     * @var string $role
     *
     * @ORM\Column(name="role", type="string", length=255)
     */
    private $role;

    /**
     * @var string $sentMessages
     *
     * @ORM\Column(name="sentMessages", type="string", length=255)
     */
    private $sentMessages;

    /**
     * @var string $receivedMessages
     *
     * @ORM\Column(name="receivedMessages", type="string", length=255)
     */
    private $receivedMessages;

    /**
     * @var string $notifications
     *
     * @ORM\Column(name="notifications", type="string", length=255)
     */
    private $notifications;

    /**
     * @var string $relations
     *
     * @ORM\Column(name="relations", type="string", length=255)
     */
    private $relations;

    /**
     * @var string $items
     *
     * @ORM\Column(name="items", type="string", length=255)
     */
    private $items;

    /**
     * @var string $editableItems
     *
     * @ORM\Column(name="editableItems", type="string", length=255)
     */
    private $editableItems;

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
     * Set avatar
     *
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
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
}