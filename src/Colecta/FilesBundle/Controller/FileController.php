<?php

namespace Colecta\FilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        
        $form = $this->uploadAction();
        
        return $this->render('ColectaFilesBundle:File:new.html.twig', array('categories' => $categories, 'form' => $form));
    }
    public function uploadAction()
    {
        $request = $this->getRequest();
        $item = new File();
        
        $form = $this->createForm(new FileType(), $item);
        
        if ($request->getMethod() == 'POST') {
            $user = $this->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getEntityManager();
            
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
        
            if($user == 'anon.') 
            {
                $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            }
            elseif(!$request->get('newCategory') && !$category)
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
                    if($request->get('newCategory'))
                    {
                        $category = new Category();
                        $category->setName($request->get('newCategory'));
                        
                        //Category Slug generate
                        $catSlug = $item->generateSlug($request->get('newCategory'));
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
                    if($request->get('newFolder'))
                    {
                        $folder = new Folder();
                        $folder->setName($request->get('newFolder'));
                        
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
            }
            
            $referer = $request->headers->get('referer');
        
            if(empty($referer))
            {
                $referer = $this->generateUrl('ColectaFileIndex');
            }
            
            return new RedirectResponse($referer);
        }
        
        return $form->createView();
    }
}
