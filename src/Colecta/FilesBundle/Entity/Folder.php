<?php

namespace Colecta\FilesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\FilesBundle\Entity\Folder
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Folder extends \Colecta\ItemBundle\Entity\Item
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
     * @var boolean $public
     *
     * @ORM\Column(name="public", type="boolean")
     */
    private $public;

    /**
     * @var boolean $personal
     *
     * @ORM\Column(name="personal", type="boolean")
     */
    private $personal;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="folder")
     */
    private $files;


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
     * Set public
     *
     * @param boolean $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * Get public
     *
     * @return boolean 
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set personal
     *
     * @param boolean $personal
     */
    public function setPersonal($personal)
    {
        $this->personal = $personal;
    }

    /**
     * Get personal
     *
     * @return boolean 
     */
    public function getPersonal()
    {
        return $this->personal;
    }

    /**
     * Set files
     *
     * @param string $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * Get files
     *
     * @return string 
     */
    public function getFiles()
    {
        return $this->files;
    }
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add files
     *
     * @param Colecta\FilesBundle\Entity\File $files
     */
    public function addFile(\Colecta\FilesBundle\Entity\File $files)
    {
        $this->files[] = $files;
    }
    
    public function getType()
    {
        return 'Files/Folder';
    }
}