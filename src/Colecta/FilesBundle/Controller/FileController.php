<?php

namespace Colecta\FilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
        
        $item = new File();
        $form = $this->createForm(new FileType(), $item);
        
        return $this->render('ColectaFilesBundle:File:new.html.twig', array('categories' => $categories, 'form' => $form->createView()));
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
        $form = $this->createForm(new FileType(), $item);
        
        if ($request->getMethod() == 'POST') 
        {
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->request->get('category'));
            
            if(!$request->get('newCategory') && !$category)
            {
                $this->get('session')->setFlash('error', 'No existe la categoria');
            }
            else
            {
                $form->bindRequest($request);
                
                if ($form->isValid()) 
                {
                    $item->upload();
                    
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
                    $item->getFolder()->setDate(new \DateTime('now'));
                    
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
            
                    $em->persist($item);
                    $em->flush();
                
                    return new RedirectResponse($this->generateUrl('ColectaFileView', array('slug' => $item->getSlug())));
                }
                else
                {
                    $uplmaxsize = ini_get('upload_max_filesize');
                    $this->get('session')->setFlash('error', 'El tamaño del archivo debe ser inferior a '.$uplmaxsize);
                }
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaFilesBundle:File:new.html.twig', array('categories' => $categories, 'form' => $form->createView()));
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
