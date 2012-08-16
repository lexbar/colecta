<?php

namespace Colecta\FilesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\FilesBundle\Entity\Folder
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Folder
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
     * @var string $files
     *
     * @ORM\Column(name="files", type="string", length=255)
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
}