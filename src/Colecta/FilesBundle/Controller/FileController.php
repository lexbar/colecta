<?php

namespace Colecta\FilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Colecta\FilesBundle\Form\Frontend\FileType;
use Colecta\FilesBundle\Entity\File;
use Colecta\ItemBundle\Entity\Item;
use Colecta\ItemBundle\Entity\Relation;
use Colecta\FilesBundle\Entity\Folder;
use Colecta\ItemBundle\Entity\Category;

class FileController extends Controller
{
    private $ipp = 10; //Items per page
    
    public function indexAction()
    {
        return $this->pageAction(1);
    }
    
    public function pageAction($page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaFilesBundle:Folder')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        $count = count($items);
        
        //Pagination
        if($count > $this->ipp) 
        {
            $thereAreMore = true;
            unset($items[$this->ipp]);
        }
        else
        {
            $thereAreMore = false;
        }
        
        return $this->render('ColectaFilesBundle:File:index.html.twig', array('items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        return $this->render('ColectaFilesBundle:File:full.html.twig', array('item' => $item));
    }
    public function newAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.') 
        {
            $this->get('session')->getFlashBag()->add('error', 'Debes iniciar sesión');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST')
        {           
            
            //New Folder
                        
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->request->get('category'));
            
            $item = new Folder();
            
            if(! $request->request->get('name') )
            {
                $item->setName($user->getName() . ' ' . date("d/m/Y"));
            }
            else
            {
                $item->setName($request->request->get('name'));
            }
            
            //Slug generation
            $slug = $item->generateSlug();
            $n = 2;
            
            while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
            {
                if($n > 2)
                {
                    $subtract = $num_length = strlen((string)$n) + 1 ;
                    $slug = substr($slug,0,-$subtract);
                }
                
                $slug .= '-'.$n;
                
                $n++;
            }
            $item->setSlug($slug);
            
            $item->setAuthor($user);
            $item->summarize(strval($request->request->get('text')));
            $item->setText($request->request->get('text'));
            $item->setAllowComments(true);
            $item->setDraft(true);
            $item->setPart(false);
            $item->setPublic(true);
            $item->setPersonal(false);
            $item->setDate(new \DateTime('now'));
            
            //New Category
            if($request->request->get('newCategory'))
            {
                $category = new Category();
                $category->setName($request->request->get('newCategory'));
                
                //Category Slug generate
                $catSlug = $item->generateSlug($request->request->get('newCategory'));
                $n = 2;
                
                while($em->getRepository('ColectaItemBundle:Category')->findOneBySlug($catSlug)) 
                {
                    if($n > 2)
                    {
                        $catSlug = substr($catSlug,0,-2);
                    }
                    
                    $catSlug .= '-'.$n;
                    
                    $n++;
                }
            
                $category->setSlug($catSlug);
                $category->setText('');
                $category->setLastchange(new \DateTime('now'));
            }
            
            $em->persist($category); 
            $item->setCategory($category);
            
            if($request->get('attachTo'))
            {
                $itemRelated = $em->getRepository('ColectaItemBundle:Item')->findOneById($request->get('attachTo'));
                $relation = new Relation();
                $relation->setUser($user);
                $relation->setItemto($itemRelated);
                $relation->setItemfrom($item);
                $relation->setText($itemRelated->getName());
                
                $em->persist($relation);
                
                $item->setPart(true);
            }
            
            $em->persist($item);
            $em->flush();
            
            if(isset($_FILES['file']) && (is_array($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'][0]) || ! is_array($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])))
            {
                return $this->pickAction($item->getSlug());
            }
            
            // Process the files 
            return $this->XHRProcessAction($item->getSlug());
        }
        else
        {
            return $this->render('ColectaFilesBundle:File:pick.html.twig');
        }
    }
    public function pickAction($slug) //slug of the destiny folder
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.') 
        {
            $this->get('session')->getFlashBag()->add('error', 'Debes iniciar sesión');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $folder = $em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug);
        
        if(!$folder)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe la carpeta indicada.');
            return new RedirectResponse($this->generateUrl('ColectaFileNew'));
        }
        elseif($folder->getAuthor() != $user && ($folder->getPersonal() || $folder->getDraft()))
        {
            $this->get('session')->getFlashBag()->add('error', 'No puedes publicar en esta carpeta.');
            return new RedirectResponse($this->generateUrl('ColectaFileNew'));
        }
        
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST')
        {
            $token = $this->XHRUploadAction()->getContent();
            
            $TheFiles = $this->get('session')->get('TheFiles');
            if(is_array($TheFiles))
            {
                $newTheFiles = array();
                $i = 0;
                
                foreach($TheFiles as $f)
                {
                    if(!$request->request->get('file'.$i.'Delete'))
                    {
                        $newTheFiles[] = array('name'=>$request->request->get('file'.$i.'Name'),'text'=>$request->request->get('file'.$i.'Text'),'token'=>$f['token']);
                    }
                    
                    $i++;
                }
                
                $newTheFiles[] = array('name'=>'','text'=>'','token'=>$token);
                
                $TheFiles = $newTheFiles;
            }
            else
            {
                $TheFiles = array(array('name'=>'','text'=>'','token'=>$token));
            }
            
            $this->get('session')->set('TheFiles',$TheFiles);
        }        
        
        return $this->render('ColectaFilesBundle:File:pick.html.twig', array('folder' => $folder));
    }
    public function XHRUploadAction() 
    {
        //Single file upload from XHR form
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.') 
        {
            throw $this->createNotFoundException();
        }
        
        set_time_limit(60*5);

        //Only first file
        if(is_array($_FILES['file']['tmp_name']))
        {
            $file = new UploadedFile($_FILES['file']['tmp_name'][0],$_FILES['file']['name'][0],$_FILES['file']['type'][0],$_FILES['file']['size'][0],$_FILES['file']['error'][0]);
        }
        else
        {
            $file = new UploadedFile($_FILES['file']['tmp_name'],$_FILES['file']['name'],$_FILES['file']['type'],$_FILES['file']['size'],$_FILES['file']['error']);
        } 
        
        $cachePath = __DIR__ . '/../../../../app/cache/prod/images/' ;
        $filename = 'xhr-' . md5($file->getClientOriginalName() . $_FILES['file']['tmp_name']);
        
        $extension = str_replace('jpg', 'jpeg', $file->guessExtension() );
        if(empty($extension)) 
        {
            $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        }
        
        $file->move($cachePath, $filename.'.'.$extension);
        
        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent($filename.'.'.$extension);
        $response->headers->set('Content-Type', 'text/plain');
        
        return $response;
    }
    public function XHRPreviewAction($token)
    {
        $this->get('request')->setRequestFormat('image');
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.') 
        {
            throw $this->createNotFoundException();
        }
        
        $cachePath = __DIR__ . '/../../../../app/cache/prod/images/' ;
        
        if(!file_exists($cachePath.$token))
        {
            throw $this->createNotFoundException();
        }
        
        $is = getimagesize($cachePath.$token);
        if(!in_array($is[2] , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG))) 
        {
            $image = new \Imagick(__DIR__ . '/../../../../web/img/novisible.png');
        }
        else
        {
            $image = new \Imagick($cachePath.$token);
        }
        
        autoRotateImage($image, $cachePath.$token);
        $image->cropThumbnailImage(250, 190);
        $image->setImagePage(0, 0, 0, 0);
        $image->normalizeImage();
        $image->setImageFormat('jpeg'); 
        
        $response = new Response();
        
        $response->setStatusCode(200);
        $response->setContent($image);
        $response->headers->set('Content-Type', 'image/jpeg');
        
        return $response;
    }
    public function XHRProcessAction($slug) 
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.') 
        {
            $this->get('session')->getFlashBag()->add('error', 'Debes iniciar sesión');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $folder = $em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug);
        
        if(!$folder )
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe la carpeta indicada.');
            return new RedirectResponse($this->generateUrl('ColectaFileNew'));
        }
        elseif($folder->getPublic() == false || $folder->getAuthor() != $user && ($folder->getPersonal() || $folder->getDraft()))
        {
            $this->get('session')->getFlashBag()->add('error', 'No puedes publicar en esta carpeta.');
            return new RedirectResponse($this->generateUrl('ColectaFileNew'));
        }
        
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST')
        {
            if(! $request->request->get('file0Token') )
            {
                $this->get('session')->getFlashBag()->add('error', 'Ha ocurrido un error enviando el formulario.');
                return new RedirectResponse($this->generateUrl('ColectaFilePick', array('slug'=>$slug)));
            }
            
            $i = 0;
            
            while($request->request->get('file'.$i.'Token')) //while there are more files...
            {
                $cachePath = __DIR__ . '/../../../../app/cache/prod/images/' ;
                $uploadPath = __DIR__ . '/../../../../web/uploads/files/';
                $token = $request->request->get('file'.$i.'Token').'';
                $extension = strtolower(pathinfo($token, PATHINFO_EXTENSION));
                $hashName = sha1($token . mt_rand(0, 99999)).'.'.$extension;
                
                if($request->request->get('file'.$i.'Delete') == 1)
                {
                    if(file_exists($cachePath.$token))
                    {
                        //Delete file
                        unlink($cachePath.$token);
                    }
                }
                else
                {
                    rename($cachePath.$token,$uploadPath.$hashName);
                    
                    $item = new File();
                    
                    if($request->request->get('file'.$i.'Name') == '' || $request->request->get('file'.$i.'Name') == 'Nombre...')
                    {
                        $name = $folder->getName();
                    }
                    else
                    {
                        $name = $request->request->get('file'.$i.'Name');
                    }
                    
                    $item->setName($name);
                    
                    if($request->request->get('file'.$i.'Text') == '' || $request->request->get('file'.$i.'Text') == 'Descripción...')
                    {
                        $text = '';
                    }
                    else
                    {
                        $text = strval($request->request->get('file'.$i.'Text'));
                    }
                    
                    $item->setText($text);
                    
                    //Slug generation
                    $slug = $item->generateSlug();
                    $n = 2;
                    
                    while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
                    {
                        if($n > 2)
                        {
                            $subtract = $num_length = strlen((string)$n) + 1 ;
                            $slug = substr($slug,0,-$subtract);
                        }
                        
                        $slug .= '-'.$n;
                        
                        $n++;
                    }
                    $item->setSlug($slug);
                    
                    $item->setAuthor($user);
                    $item->summarize($item->getText());
                    $item->setAllowComments(true);
                    $item->setDraft(false);
                    $item->setDate(new \DateTime('now'));
                    
                    $item->setFilename($hashName);
                    $item->setFiletype($extension);
                    
                    $item->setFolder($folder);
                    if($folder->getDraft())
                    {
                        $folder->setDraft(0);
                       
                    }
                    $em->persist($folder);
                    
                    $folder->setDate(new \DateTime('now'));
                    $item->setPart(true);
                    
                    $item->setCategory($folder->getCategory());
                    $folder->getCategory()->setLastchange(new \DateTime('now'));
                    $em->persist($folder->getCategory());  
                    
                    $em->persist($item);
                    $em->flush();
                }
                
                $i++;
            }
            
            
            if($this->get('session')->get('TheFiles'))
            {
                $this->get('session')->set('TheFiles',null);
            }
            return new RedirectResponse($this->generateUrl('ColectaFolderView', array('slug'=>$folder->getSlug())));
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', 'Ha ocurrido un error enviando el formulario.');
            return new RedirectResponse($this->generateUrl('ColectaFilePick', array('slug'=>$slug)));
        }
        
        return $this->render('ColectaFilesBundle:File:pick.html.twig', array('folder' => $folder));
    }
    public function editAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->getRequest();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        if($user == 'anon.' || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaFileView', array('slug' => $item->getSlug())));
        }
        
        if ($request->getMethod() == 'POST') 
        {
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->request->get('category'));
            $folder = $em->getRepository('ColectaFilesBundle:Folder')->findOneById($request->request->get('folder'));
            
            $item->setName($request->request->get('name'));
            $item->setText($request->request->get('text'));
            $item->setFolder($folder);
            $item->setCategory($category);
            
            if(!$request->get('newCategory') && !$category)
            {
                $this->get('session')->getFlashBag()->add('error', 'No existe la categoria');
            }
            elseif(!$request->get('newFolder') && !$folder)
            {
                $this->get('session')->getFlashBag()->add('error', 'No existe la carpeta');
            }
            else
            {
                if($item->isValid())
                {
                    //New Category
                    if($request->request->get('newCategory'))
                    {
                        $category = new Category();
                        $category->setName($request->request->get('newCategory'));
                        
                        //Category Slug generate
                        $catSlug = $item->generateSlug($request->request->get('newCategory'));
                        $n = 2;
                        
                        while($em->getRepository('ColectaItemBundle:Category')->findOneBySlug($catSlug)) 
                        {
                            if($n > 2)
                            {
                                $catSlug = substr($catSlug,0,-2);
                            }
                            
                            $catSlug .= '-'.$n;
                            
                            $n++;
                        }
                    
                        $category->setSlug($catSlug);
                        $category->setText('');
                    }
                    
                    $category->setLastchange(new \DateTime('now'));
                    $em->persist($category); 
                    $item->setCategory($category);
                    
                    //New Folder
                    if($request->request->get('newFolder'))
                    {
                        $folder = new Folder();
                        $folder->setName($request->request->get('newFolder'));
                        
                        //Slug generation
                        $slug = $folder->generateSlug();
                        $n = 2;
                        
                        while($em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug)) 
                        {
                            if($n > 2)
                            {
                                $subtract = $num_length = strlen((string)$n) + 1 ;
                                $slug = substr($slug,0,-$subtract);
                            }
                            
                            $slug .= '-'.$n;
                            
                            $n++;
                        }
                        $folder->setSlug($slug);
                        // End slug generation
                        $folder->setSummary('');
                        $folder->setTagwords('');
                        $folder->setAllowComments(1);
                        $folder->setDraft(0);
                        $folder->setPart(0);
                        $folder->setPublic(1);
                        $folder->setPersonal(0);
                        
                        $folder->setCategory($category);
                        $folder->setAuthor($user);
                        
                        
                        $em->persist($folder);
                        
                        $item->setFolder($folder);
                    }
                    else
                    {
                        if($folder->getDraft())
                        {
                            $folder->setDraft(0);
                            $em->persist($folder);
                        }
                    }
                    
                    $category->setLastchange(new \DateTime('now'));
                    $em->persist($category);                     
                    
                    $em->persist($item);
                    $em->flush();
                    
                    // Update all categories. 
                    // This is done this way because I'm lazy and so that every time an item is created or modified consistency is granted.
                
                    $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
                    
                    $this->get('session')->getFlashBag()->add('success', 'Archivo modificado correctamente');             
                }
                else
                {
                    //$uplmaxsize = ini_get('upload_max_filesize');
                    $this->get('session')->getFlashBag()->add('error', 'El formulario no es válido.');
                }
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        $folders = $em->getRepository('ColectaFilesBundle:Folder')->findAll();
        
        return $this->render('ColectaFilesBundle:File:edit.html.twig', array('item' => $item, 'categories' => $categories, 'folders' => $folders));
    }
    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        if($user == 'anon.' || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaFileView', array('slug' => $item->getSlug())));
        }
        
        if($item->getFolder())
        {
            $returnURL = $this->generateUrl('ColectaFolderView', array('slug' => $item->getFolder()->getSlug()));
        }
        else
        {
            $returnURL = $this->generateUrl('ColectaFileIndex');
        }
        
        $em->remove($item);
        $em->flush();
        
        
        return new RedirectResponse($returnURL);
    }
    public function uploadAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.') 
        {
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        
        $item = new File();
        
        if ($request->getMethod() == 'POST') 
        {
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->request->get('category'));
            $folder = $em->getRepository('ColectaFilesBundle:Folder')->findOneById($request->request->get('folder'));
            
            $item->setName($request->request->get('name'));
            $item->setText($request->request->get('text'));
            $item->setFolder($folder);
            $item->setCategory($category);
            $item->setFile(new UploadedFile($_FILES['files']['tmp_name'][0],$_FILES['files']['name'][0],$_FILES['files']['type'][0],$_FILES['files']['size'][0],$_FILES['files']['error'][0]));
            
            if(!$request->get('newCategory') && !$category)
            {
                $this->get('session')->getFlashBag()->add('error', 'No existe la categoria');
            }
            elseif(!$request->get('newFolder') && !$folder)
            {
                $this->get('session')->getFlashBag()->add('error', 'No existe la carpeta');
            }
            else
            {
                if($item->isValid())
                {
                    //New Category
                    if($request->request->get('newCategory'))
                    {
                        $category = new Category();
                        $category->setName($request->request->get('newCategory'));
                        
                        //Category Slug generate
                        $catSlug = $item->generateSlug($request->request->get('newCategory'));
                        $n = 2;
                        
                        while($em->getRepository('ColectaItemBundle:Category')->findOneBySlug($catSlug)) 
                        {
                            if($n > 2)
                            {
                                $catSlug = substr($catSlug,0,-2);
                            }
                            
                            $catSlug .= '-'.$n;
                            
                            $n++;
                        }
                    
                        $category->setSlug($catSlug);
                        $category->setText('');
                    }
                    
                    $category->setLastchange(new \DateTime('now'));
                    $em->persist($category); 
                    $item->setCategory($category);
                    
                    //New Folder
                    if($request->request->get('newFolder'))
                    {
                        $folder = new Folder();
                        $folder->setName($request->request->get('newFolder'));
                        
                        //Slug generation
                        $slug = $folder->generateSlug();
                        $n = 2;
                        
                        while($em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug)) 
                        {
                            if($n > 2)
                            {
                                $subtract = $num_length = strlen((string)$n) + 1 ;
                                $slug = substr($slug,0,-$subtract);
                            }
                            
                            $slug .= '-'.$n;
                            
                            $n++;
                        }
                        $folder->setSlug($slug);
                        // End slug generation
                        $folder->summarize($item->getText());
                        $folder->setAllowComments(1);
                        $folder->setDraft(0);
                        $folder->setPart(0);
                        $folder->setPublic(1);
                        $folder->setPersonal(0);
                        
                        $folder->setCategory($category);
                        $folder->setAuthor($user);
                        
                        
                        $em->persist($folder);
                        
                        $item->setFolder($folder);
                    }
                    else
                    {
                        if($folder->getDraft())
                        {
                            $folder->setDraft(0);
                            $em->persist($folder);
                        }
                    }
                    
                    $item->getFolder()->setDate(new \DateTime('now'));
                    $item->setPart(true);
                    
                    $category->setLastchange(new \DateTime('now'));
                    $em->persist($category);                     
                    
                    
                    //Slug generation
                    $slug = $item->generateSlug();
                    $n = 2;
                    
                    while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
                    {
                        if($n > 2)
                        {
                            $subtract = $num_length = strlen((string)$n) + 1 ;
                            $slug = substr($slug,0,-$subtract);
                        }
                        
                        $slug .= '-'.$n;
                        
                        $n++;
                    }
                    $item->setSlug($slug);
                    
                    $item->setAuthor($user);
                    $item->summarize($item->getText());
                    $item->setAllowComments(true);
                    $item->setDraft(false);
                    
                    $item->upload();
                    
                    $em->persist($item);
                    $em->flush();
                    
                    if(count($_FILES['files']['name']) > 1) //if multiple upload
                    {
                        for($i = 1; $i < count($_FILES['files']['name']); $i++)
                        {
                            $newItem = clone $item;
                            
                            //new slug
                            $slug = substr($slug,0,-2).'-'.$n; $n++;
                            while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
                            {
                                if($n > 2)
                                {
                                    $subtract = $num_length = strlen((string)$n) + 1 ;
                                    $slug = substr($slug,0,-$subtract);
                                }
                                
                                $slug .= '-'.$n;
                                
                                $n++;
                            }
                            $newItem->setSlug($slug);
                            
                            $newItem->setFile(new UploadedFile($_FILES['files']['tmp_name'][$i],$_FILES['files']['name'][$i],$_FILES['files']['type'][$i],$_FILES['files']['size'][$i],$_FILES['files']['error'][$i]));
                            $newItem->upload();
                            
                            $em->persist($newItem);
                            $em->flush();
                        }
                        
                        // Update all categories. 
                        // This is done this way because I'm lazy and so that every time an item is created or modified consistency is granted.
                    
                        $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
                        
                        //ItemSearch INSERT
                        $sql = "INSERT INTO ItemSearch VALUES(:id, :name, :text)";
                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->bindValue('id', $item->getId());
                        $stmt->bindValue('name', $item->getName());
                        $stmt->bindValue('text', $item->getText());
                        $stmt->execute();
                        
                        return new RedirectResponse($this->generateUrl('ColectaFolderView', array('slug' => $item->getFolder()->getSlug())));
                    }
                    else
                    {
                        // Update all categories. 
                        // This is done this way because I'm lazy and so that every time an item is created or modified consistency is granted.
                    
                        $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
                        
                        //ItemSearch INSERT
                        $sql = "INSERT INTO ItemSearch VALUES(:id, :name, :text)";
                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->bindValue('id', $item->getId());
                        $stmt->bindValue('name', $item->getName());
                        $stmt->bindValue('text', $item->getText());
                        $stmt->execute();
                        
                        return new RedirectResponse($this->generateUrl('ColectaFileView', array('slug' => $item->getSlug())));
                    }
                }
                else
                {
                    $uplmaxsize = ini_get('upload_max_filesize');
                    $this->get('session')->getFlashBag()->add('error', 'El formulario no es válido.');
                }
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        $folders = $em->getRepository('ColectaFilesBundle:Folder')->findAll();
        
        return $this->render('ColectaFilesBundle:File:new.html.twig', array('categories' => $categories, 'folders' => $folders, 'item' => $item));
    }
    public function thumbnailAction($slug, $width, $height)
    {    
        $this->get('request')->setRequestFormat('image');
        
        $cachePath = __DIR__ . '/../../../../app/cache/prod/images/thumbnail-' . $slug . '_' . $width . 'x' . $height ;
        
        $response = new Response();
        
        if(@filemtime($cachePath))
        {
            $response->setLastModified(new \DateTime(date("F d Y H:i:s.",filemtime($cachePath))));
        }
        
        $response->setPublic();
        
        if ($response->isNotModified($this->getRequest())) {
            return $response; // this will return the 304 if the cache is OK
        } 
        
        if(file_exists($cachePath))
        {
            $image = file_get_contents($cachePath);
            
            $response->setContent($image);
            $response->headers->set('Content-Type', mime_content_type($cachePath) );
            
            return $response;
        }
        else
        {   
            
            $em = $this->getDoctrine()->getManager();
            $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
            
            if(!$item)
            {
                throw $this->createNotFoundException('El archivo no existe');
            }
            
            $image = new \Imagick($item->getAbsolutePath());
            autoRotateImage($image, $item->getAbsolutePath());
            $image->cropThumbnailImage($width, $height);
            $image->setImagePage(0, 0, 0, 0);
            $image->normalizeImage();
            
            //fill out the cache
            file_put_contents($cachePath, $image);
            
            
            $response->setStatusCode(200);
            $response->setContent($image);
            $response->headers->set('Content-Type', mime_content_type( $item->getAbsolutePath() ));
            
            
            return $response;
        }
    }
    public function resizeAction($slug, $width, $height) //max width and height
    {
        $this->get('request')->setRequestFormat('image');
        
        $cachePath = __DIR__ . '/../../../../app/cache/prod/images/bestfit-' . $slug . '_' . $width . 'x' . $height ;
        
        $response = new Response();
        
        if(@filemtime($cachePath))
        {
            $response->setLastModified(new \DateTime(date("F d Y H:i:s.",filemtime($cachePath))));
        }
        
        $response->setPublic();
        
        if ($response->isNotModified($this->getRequest())) {
            return $response; // this will return the 304 if the cache is OK
        } 
        
        if(file_exists($cachePath))
        {
            $image = file_get_contents($cachePath);
            
            $response->setContent($image);
            
            $response->headers->set('Content-Type', mime_content_type($cachePath) );
            
            return $response;
        }
        else
        {   
            
            $em = $this->getDoctrine()->getManager();
            
            $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
            
            if(!$item)
            {
                throw $this->createNotFoundException('El archivo no existe');
            }
            
            $image = new \Imagick($item->getAbsolutePath());
            autoRotateImage($image, $item->getAbsolutePath());
            $image->setImageResolution(72,72); 
            $image->scaleImage($width, $height, true);
            $image->setImagePage(0, 0, 0, 0);
            
            //fill out the cache
            file_put_contents($cachePath, $image);
            
            $response = new Response();
            
            $response->setStatusCode(200);
            $response->setContent($image);
            $response->headers->set('Content-Type', mime_content_type( $item->getAbsolutePath() ));
            
            return $response;
        }
    }
    public function downloadAction($slug,$type)
    {
        $em = $this->getDoctrine()->getManager();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        if(!$item)
        {
            throw $this->createNotFoundException('El archivo no existe');
        }

        $response = new Response();
        
        $response->setStatusCode(200);
        $response->setContent(file_get_contents($item->getAbsolutePath()));
        $response->headers->set('Content-Type', mime_content_type( $item->getAbsolutePath() ));
        $response->headers->set('Content-Text', 'Descarga de '.$item->getName());
        $response->headers->set('Content-Disposition', 'attachment; filename='.$item->getSlug().'.'.$type);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }
}

function autoRotateImage($image, $path) 
{
    $exif = (function_exists('exif_read_data')) ? @exif_read_data( $path ) : array();
    if(!empty($exif['Orientation'])) 
    {
        switch($exif['Orientation']) 
        {
            case 8:
                $image->rotateimage("#000", -90);
                break;
            case 3:
                $image->rotateimage("#000", 180);
                break;
            case 6:
                $image->rotateimage("#000", 90);
                break;
        }
    }
} 