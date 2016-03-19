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
    protected $filename;
    
    /**
     * @Assert\File(maxSize="32M",maxSizeMessage="Allowed maximum size is {{ limit }}")
     */
    protected $file;
    
    /**
     * @var string $filetype
     *
     * @ORM\Column(name="filetype", type="string", length=4)
     */
    protected $filetype;

    /**
     * @var text $text
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
    * @ORM\ManyToOne(targetEntity="Folder")
    * @ORM\JoinColumn(name="folder_id", referencedColumnName="id") 
    */
    protected $folder;
    
    //Heritage
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var boolean
     */
    protected $part;

    /**
     * @var string
     */
    protected $summary;

    /**
     * @var string
     */
    protected $tagwords;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var boolean
     */
    protected $allowComments;

    /**
     * @var boolean
     */
    protected $draft;

    /**
     * @var \Colecta\ActivityBundle\Entity\Category
     */
    protected $category;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $relatedto;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $relatedfrom;

    /**
     * @var \Colecta\UserBundle\Entity\User
     */
    protected $author;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $editors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $likers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $comments;
        
    public function getType()
    {
        return 'Files/File';
    }
    
    public function getViewPath()
    {
        return 'ColectaFileView';
    }
    
    public function getExif()
    {
        return (function_exists('exif_read_data')) ?  exif_read_data( $this->getAbsolutePath() ) : array();
    }
    
    public function isImage()
    {
        return in_array($this->getFiletype(), array('jpg', 'jpeg', 'png', 'gif', 'tiff'));
    }
    
    // File upload
    public function getURLPath() 
    {
        return '/'.$this->getWebPath();
    }
    
    protected function getUploadDir() 
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
    
    protected function getUploadRootDir() 
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
        
        $extension = str_replace('jpg', 'jpeg', $this->file->guessExtension() );
        
        if(empty($extension)) 
        {
            $extension = strtolower(pathinfo($this->file->getClientOriginalName(), PATHINFO_EXTENSION));
        }
        
        $this->setFiletype($extension);
        
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
    
    public function isValid()
    {
        return true;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return File
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    
        return $this;
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
     * @return File
     */
    public function setFiletype($filetype)
    {
        $this->filetype = $filetype;
    
        return $this;
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
     * Set text
     *
     * @param string $text
     * @return File
     */
    public function setText($text)
    {
        $text = str_replace(array("<br>","<br />"),"\n",str_replace("\n",'',$text));
        $text = preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "\n", $text));
        $this->text = $text;
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
     * Set folder
     *
     * @param \Colecta\FilesBundle\Entity\Folder $folder
     * @return File
     */
    public function setFolder(\Colecta\FilesBundle\Entity\Folder $folder = null)
    {
        $this->folder = $folder;
    
        return $this;
    }

    /**
     * Get folder
     *
     * @return \Colecta\FilesBundle\Entity\Folder 
     */
    public function getFolder()
    {
        return $this->folder;
    }
    
    public function getOpen()
    {
        return $this->open && $this->getFolder()->getOpen();
    }
}