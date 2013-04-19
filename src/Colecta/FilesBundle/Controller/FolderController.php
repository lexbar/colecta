<?php

namespace Colecta\FilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\FilesBundle\Entity\Folder;


class FolderController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaFilesBundle:Folder')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaFilesBundle:Folder:index.html.twig', array('items' => $items));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug);
        
        return $this->render('ColectaFilesBundle:Folder:full.html.twig', array('item' => $item));
    }
    public function editAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug);
        
        $this->get('session')->setFlash('success', 'No es posible editar carpetas todavía. Disculpa las molestias.');
        
        return $this->render('ColectaFilesBundle:Folder:full.html.twig', array('item' => $item));
    }
    public function formlistAction($selected)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $this->get('session')->setFlash('error', 'Debes iniciar sesión');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        if($selected == 0)
        {
            $personal = $em->getRepository('ColectaFilesBundle:Folder')->findOneBy(array('personal'=>1,'author'=>$user));
            
            if($personal)
            {
                $selected = $personal->getId();
            }
            else
            {
                //create a new personal folder
                $folder = new Folder();
                $folder->setName('Carpeta de '.$user->getName());
                
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
                $folder->setDraft(1);
                $folder->setPart(0);
                $folder->setPublic(1);
                $folder->setPersonal(1);
                
                $folder->setCategory($em->getRepository('ColectaFilesBundle:Folder')->findOneById(1));
                $folder->setAuthor($user);
                
                
                $em->persist($folder);
                $em->flush();
                
                $selected = $folder->getId();
            }
        }
        
        $folders = $em->createQuery("SELECT f FROM ColectaFilesBundle:Folder f WHERE f.author = :user OR (f.public = 1 AND f.personal = 0 AND f.draft = 0) ORDER BY f.date DESC")->setParameter('user',$user->getId())->getResult();
        
        return $this->render('ColectaFilesBundle:Folder:folderselect.html.twig', array('folders'=>$folders, 'selected'=>$selected));
    }
}
