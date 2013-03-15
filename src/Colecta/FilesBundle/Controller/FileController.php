<?php

namespace Colecta\FilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Colecta\FilesBundle\Form\Frontend\FileType;
use Colecta\FilesBundle\Entity\File;
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
        
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaFilesBundle:File')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        //Pagination
        if(count($items) > $this->ipp) 
        {
            $thereAreMore = true;
            unset($items[$this->ipp]);
        }
        else
        {
            $thereAreMore = false;
        }
        
        $form = $this->uploadAction();
        
        return $this->render('ColectaFilesBundle:File:index.html.twig', array('items' => $items, 'categories' => $categories,'form' => $form, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        return $this->render('ColectaFilesBundle:File:full.html.twig', array('item' => $item));
    }
    public function newAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        $folders = $em->getRepository('ColectaFilesBundle:Folder')->findAll();
        
        $item = new File();
        
        return $this->render('ColectaFilesBundle:File:new.html.twig', array('categories' => $categories, 'folders' => $folders, 'item' => $item));
    }
    public function editAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->getRequest();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        if($user == 'anon.') 
        {
            $this->get('session')->setFlash('error', 'Debes iniciar sesión');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        elseif($user != $item->getAuthor())
        {
            return new RedirectResponse($this->generateUrl('ColectaFileView', array('slug' => $item->getSlug())));
        }
        
        if ($request->getMethod() == 'POST') 
        {
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->request->get('category'));
            $folder = $em->getRepository('ColectaFilesBundle:Folder')->findOneById($request->request->get('folder'));
            
            $item->setName($request->request->get('name'));
            $item->setDescription($request->request->get('description'));
            $item->setFolder($folder);
            $item->setCategory($category);
            
            if(!$request->get('newCategory') && !$category)
            {
                $this->get('session')->setFlash('error', 'No existe la categoria');
            }
            elseif(!$request->get('newFolder') && !$folder)
            {
                $this->get('session')->setFlash('error', 'No existe la carpeta');
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
                            
                            $catSlug .= '_'.$n;
                            
                            $n++;
                        }
                    
                        $category->setSlug($catSlug);
                        $category->setDescription('');
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
                                $slug = substr($slug,0,-2);
                            }
                            
                            $slug .= '_'.$n;
                            
                            $n++;
                        }
                        $folder->setSlug($slug);
                        // End slug generation
                        $folder->setSummary('');
                        $folder->setTagwords('');
                        $folder->setAllowComments(1);
                        $folder->setDraft(0);
                        $folder->setPublic(1);
                        $folder->setPersonal(0);
                        
                        $folder->setCategory($category);
                        $folder->setAuthor($user);
                        
                        
                        $em->persist($folder);
                        
                        $item->setFolder($folder);
                    }
                    if($item->getFolder())
                    {
                        $item->getFolder()->setDate(new \DateTime('now'));
                    }
                    
                    $category->setLastchange(new \DateTime('now'));
                    $em->persist($category);                     
                    
                    $em->persist($item);
                    $em->flush();
                    
                    $this->get('session')->setFlash('success', 'Archivo modificado correctamente');
                    return new RedirectResponse($this->generateUrl('ColectaFileView', array('slug' => $item->getSlug())));                  
                }
                else
                {
                    $uplmaxsize = ini_get('upload_max_filesize');
                    $this->get('session')->setFlash('error', 'El formulario no es válido.');
                }
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        $folders = $em->getRepository('ColectaFilesBundle:Folder')->findAll();
        
        return $this->render('ColectaFilesBundle:File:edit.html.twig', array('item' => $item, 'categories' => $categories, 'folders' => $folders));
    }
    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        if($user == 'anon.') 
        {
            $this->get('session')->setFlash('error', 'Debes iniciar sesión');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        elseif($user != $item->getAuthor())
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
        
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        
        $item = new File();
        
        if ($request->getMethod() == 'POST') 
        {
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->request->get('category'));
            $folder = $em->getRepository('ColectaFilesBundle:Folder')->findOneById($request->request->get('folder'));
            
            $item->setName($request->request->get('name'));
            $item->setDescription($request->request->get('description'));
            $item->setFolder($folder);
            $item->setCategory($category);
            $item->setFile(new UploadedFile($_FILES['files']['tmp_name'][0],$_FILES['files']['name'][0],$_FILES['files']['type'][0],$_FILES['files']['size'][0],$_FILES['files']['error'][0]));
            
            if(!$request->get('newCategory') && !$category)
            {
                $this->get('session')->setFlash('error', 'No existe la categoria');
            }
            elseif(!$request->get('newFolder') && !$folder)
            {
                $this->get('session')->setFlash('error', 'No existe la carpeta');
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
                            
                            $catSlug .= '_'.$n;
                            
                            $n++;
                        }
                    
                        $category->setSlug($catSlug);
                        $category->setDescription('');
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
                                $slug = substr($slug,0,-2);
                            }
                            
                            $slug .= '_'.$n;
                            
                            $n++;
                        }
                        $folder->setSlug($slug);
                        // End slug generation
                        $folder->setSummary('');
                        $folder->setTagwords('');
                        $folder->setAllowComments(1);
                        $folder->setDraft(0);
                        $folder->setPublic(1);
                        $folder->setPersonal(0);
                        
                        $folder->setCategory($category);
                        $folder->setAuthor($user);
                        
                        
                        $em->persist($folder);
                        
                        $item->setFolder($folder);
                    }
                    if($item->getFolder())
                    {
                        $item->getFolder()->setDate(new \DateTime('now'));
                    }
                    
                    $category->setLastchange(new \DateTime('now'));
                    $em->persist($category);                     
                    
                    
                    //Slug generation
                    $slug = $item->generateSlug();
                    $n = 2;
                    
                    while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
                    {
                        if($n > 2)
                        {
                            $slug = substr($slug,0,-2);
                        }
                        
                        $slug .= '_'.$n;
                        
                        $n++;
                    }
                    $item->setSlug($slug);
                    
                    $item->setAuthor($user);
                    $item->summarize($item->getDescription());
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
                            $slug = substr($slug,0,-2).'_'.$n; $n++;
                            while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
                            {
                                if($n > 2)
                                {
                                    $slug = substr($slug,0,-2);
                                }
                                
                                $slug .= '_'.$n;
                                
                                $n++;
                            }
                            $newItem->setSlug($slug);
                            
                            $newItem->setFile(new UploadedFile($_FILES['files']['tmp_name'][$i],$_FILES['files']['name'][$i],$_FILES['files']['type'][$i],$_FILES['files']['size'][$i],$_FILES['files']['error'][$i]));
                            $newItem->upload();
                            
                            $em->persist($newItem);
                            $em->flush();
                        }
                        
                        return new RedirectResponse($this->generateUrl('ColectaFolderView', array('slug' => $item->getFolder()->getSlug())));
                    }
                    else
                    {
                        return new RedirectResponse($this->generateUrl('ColectaFileView', array('slug' => $item->getSlug())));
                    }                    
                }
                else
                {
                    $uplmaxsize = ini_get('upload_max_filesize');
                    $this->get('session')->setFlash('error', 'El formulario no es válido.');
                }
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        $folders = $em->getRepository('ColectaFilesBundle:Folder')->findAll();
        
        return $this->render('ColectaFilesBundle:File:new.html.twig', array('categories' => $categories, 'folders' => $folders, 'item' => $item));
    }
    public function thumbnailAction($slug, $width, $height)
    {    
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
            
            $em = $this->getDoctrine()->getEntityManager();
            $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
            
            if(!$item)
            {
                throw $this->createNotFoundException('El archivo no existe');
            }
            
            $image = new \Imagick($item->getAbsolutePath());
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
            
            $response = new Response($image);
            
            $response->headers->set('Content-Type', mime_content_type($cachePath) );
            
            return $response;
        }
        else
        {   
            
            $em = $this->getDoctrine()->getEntityManager();
            
            $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
            
            if(!$item)
            {
                throw $this->createNotFoundException('El archivo no existe');
            }
            
            $image = new \Imagick($item->getAbsolutePath());
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
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        if(!$item)
        {
            throw $this->createNotFoundException('El archivo no existe');
        }

        $response = new Response();
        
        $response->setStatusCode(200);
        $response->setContent(file_get_contents($item->getAbsolutePath()));
        $response->headers->set('Content-Type', mime_content_type( $item->getAbsolutePath() ));
        $response->headers->set('Content-Description', 'Descarga de '.$item->getName());
        $response->headers->set('Content-Disposition', 'attachment; filename='.$item->getSlug().'.'.$type);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }
}
