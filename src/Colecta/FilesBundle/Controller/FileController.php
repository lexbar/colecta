<?php

namespace Colecta\FilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\FilesBundle\Form\Frontend\FileType;
use Colecta\FilesBundle\Entity\File;

class FileController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaFilesBundle:File')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        $form = $this->uploadAction();
        
        return $this->render('ColectaFilesBundle:File:index.html.twig', array('items' => $items, 'categories'=> $categories,'form' => $form));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaFilesBundle:File')->findOneBySlug($slug);
        
        return $this->render('ColectaFilesBundle:File:full.html.twig', array('item' => $item));
    }
    public function uploadAction()
    {
        $request = $this->getRequest();
        $file = new File();
        
        $form = $this->createForm(new FileType(), $file);
        
        if ($request->getMethod() == 'POST') {
            $user = $this->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getEntityManager();
            
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
        
            if(!$user) 
            {
                $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            }
            elseif(!$category)
            {
                $this->get('session')->setFlash('error', 'No existe la categoria');
            }
            else
            {
                $form->bindRequest($request);
                
                if ($form->isValid()) 
                {            
                    $file->upload();
                    
                    $file->setCategory($category);
                    $file->setAuthor($user);
                    
                    //Slug generate
                    $slug = $file->generateSlug();
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
                    $file->setSlug($slug);
                    
                    $file->summarize($file->getDescription());
                    $file->setAllowComments(true);
                    $file->setDraft(false);
            
                    $em->persist($file);
                    $em->flush();
                
                    return new RedirectResponse($this->generateUrl('ColectaFileView', array('slug' => $file->getSlug())));
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
