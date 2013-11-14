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
     * @var text $text
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text = '';
    
    /**
     * @var boolean $public
     *
     * @ORM\Column(name="public", type="boolean")
     */
    protected $public;

    /**
     * @var boolean $personal
     *
     * @ORM\Column(name="personal", type="boolean")
     */
    protected $personal;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="folder")
     */
    protected $files;
    
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
        return 'Files/Folder';
    }
    
    public function getViewPath()
    {
        return 'ColectaFolderView';
    }
    
    public function __toString()
    {
        return $this->getName();
    }
    
    public function getNextPicture(\Colecta\FilesBundle\Entity\File $file)
    {
        $files = $this->getFiles();
        
        $count = count($files);
        if($count)
        {
            $i = 0;
            $intheloop = false;
            while($files[$i] != $file && $i < $count)
            {
                $i++; 
                $intheloop = true;
            }
            
            if($i < ($count - 1) && isset($files[$i]) && $files[$i] == $file)
            {
                //$i is the key for my file in the collection
                $i++;
                while(isset($files[$i]) && !$files[$i]->isImage() && $i < $count)
                {
                    $i++;
                }
                
                if(isset($files[$i]) && $files[$i]->isImage())
                {
                    return $files[$i];
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    public function getPreviousPicture(\Colecta\FilesBundle\Entity\File $file)
    {
        $files = $this->getFiles();
        
        $count = count($files);
        if($count)
        {
            $i = 0;
            $intheloop = false;
            while($files[$i] != $file && $i < $count)
            {
                $i++; 
                $intheloop = true;
            }
            
            if($i < $count && isset($files[$i]) && $files[$i] == $file)
            {
                //$i is the key for my file in the collection
                $i--;
                while(isset($files[$i]) && !$files[$i]->isImage() && $i >= 0)
                {
                    $i--;
                }
                
                if(isset($files[$i]) && $files[$i]->isImage())
                {
                    return $files[$i];
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    public function isolateLast()
    {
        $d = $this->getDate(); // Date of the folder, set to the last file post to it
        $d->modify("-12 hours"); // select all files on the 12h previous to the last post
        
        return  $this->getFiles()->filter(
            function($file) use($d) {
                return $file->getDate() > $d; 
            }
        );
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set text
     *
     * @param string $text
     * @return Route
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
     * Set public
     *
     * @param boolean $public
     * @return Folder
     */
    public function setPublic($public)
    {
        $this->public = $public;
    
        return $this;
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
     * @return Folder
     */
    public function setPersonal($personal)
    {
        $this->personal = $personal;
    
        return $this;
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
     * Add files
     *
     * @param \Colecta\FilesBundle\Entity\File $files
     * @return Folder
     */
    public function addFile(\Colecta\FilesBundle\Entity\File $files)
    {
        $this->files[] = $files;
    
        return $this;
    }

    /**
     * Remove files
     *
     * @param \Colecta\FilesBundle\Entity\File $files
     */
    public function removeFile(\Colecta\FilesBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    public function getAuthor()
    {
        $files = $this->getFiles();
        
        if(count($files))
        {
            return $files->last()->getAuthor();
        }
        else
        {
            return $this->author;
        }
    }
}