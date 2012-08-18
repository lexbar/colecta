<?php

namespace Colecta\FilesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\FilesBundle\Entity\File
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class File extends \Colecta\ItemBundle\Entity\Item
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
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string $filetype
     *
     * @ORM\Column(name="filetype", type="string", length=4)
     */
    private $filetype;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
    * @ORM\ManyToOne(targetEntity="Folder")
    * @ORM\JoinColumn(name="folder_id", referencedColumnName="id") 
    */
    private $folder;


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
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set filetype
     *
     * @param string $filetype
     */
    public function setFiletype($filetype)
    {
        $this->filetype = $filetype;
    }

    /**
     * Get filetype
     *
     * @return string 
     */
    public function getFiletype()
    {
        return $this->filetype;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set folder
     *
     * @param string $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * Get folder
     *
     * @return string 
     */
    public function getFolder()
    {
        return $this->folder;
    }
    
    public function getType()
    {
        return 'Files/File';
    }
}