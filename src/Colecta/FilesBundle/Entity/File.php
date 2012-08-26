<?php

namespace Colecta\FilesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Colecta\FilesBundle\Entity\File
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class File extends \Colecta\ItemBundle\Entity\Item
{
    /**
     * @var string $filename
     *
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;
    
    /**
     * @Assert\Image(maxSize="10000000")
     */
    private $file;
    
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
     * Set filename
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
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
    // File upload
    public function getURLPath() 
    {
        return '/'.$this->getWebPath();
    }
    
    private function getUploadDir() 
    {
        return 'uploads/files';
    }
    
    public function getFile() 
    {
        return $this->file;
    }
    
    public function setFile($file) 
    {
        $this->file = $file;
    }
    
    public function getAbsolutePath() 
    {
        return null === $this->filename ? null : $this->getUploadRootDir() . '/' . $this->filename;
    }
    
    public function getWebPath() 
    {
        return null === $this->filename ? null : $this->getUploadDir() . '/' . $this->filename;
    }
    
    private function getUploadRootDir() 
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }
    
    public function upload() 
    {
        // the file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }
        
        $hashName = sha1($this->file->getClientOriginalName() . $this->getId() . mt_rand(0, 99999));
        
        $this->filename = $hashName . '.' . $this->file->guessExtension();
        
        $this->setFiletype($this->file->guessExtension());
        
        $this->file->move($this->getUploadRootDir(), $this->getFilename());
        
        unset($this->file);
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }
}