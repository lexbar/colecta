<?php

namespace Colecta\FilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\FilesBundle\Entity\Folder;
use Colecta\ItemBundle\Entity\Category;


class FolderController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaFilesBundle:Folder')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaFilesBundle:Folder:index.html.twig', array('items' => $items));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        
        $item = $em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug);
        
        return $this->render('ColectaFilesBundle:Folder:full.html.twig', array('item' => $item));
    }
    public function newAction()
    {
        return $this->render('ColectaItemBundle:Default:newItem.html.twig', array('type' => 'File'));
    }
    public function createAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->getRequest();
        
        if($user == 'anon.') 
        {
            $this->get('session')->getFlashBag()->add('error', 'Debes iniciar sesión');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        elseif(! $request->request->get('name'))
        {
            $this->get('session')->getFlashBag()->add('error', 'No has indicado el nombre de la carpeta');
            return new RedirectResponse($this->generateUrl('ColectaFileNew'));
        }
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->request->get('category'));
        
        $item = new Folder();        
        $item->setName($request->request->get('name'));
        
        //Slug generation
        $slug = $item->generateSlug();
        $n = 2;
        
        while($em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug)) 
        {
            if($n > 2)
            {
                $slug = substr($slug,0,-2);
            }
            
            $slug .= '-'.$n;
            
            $n++;
        }
        $item->setSlug($slug);
        
        $item->setAuthor($user);
        $item->summarize($request->request->get('text'));
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
            $category->setDescription('');
            $category->setLastchange(new \DateTime('now'));
        }
        
        $em->persist($category); 
        $item->setCategory($category);
        
        $em->persist($item);
        $em->flush();
        
        //ItemSearch INSERT
        $sql = "INSERT INTO ItemSearch VALUES(:id, :name, :text)";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue('id', $item->getId());
        $stmt->bindValue('name', $item->getName());
        $stmt->bindValue('text', $item->getText());
        $stmt->execute();
        
        return new RedirectResponse($this->generateUrl('ColectaFilePick', array('slug'=>$item->getSlug())));
    }
    public function editAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->getRequest();
        
        $item = $em->getRepository('ColectaFilesBundle:Folder')->findOneBySlug($slug);
        
        if($user == 'anon.' || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaFolderView', array('slug' => $item->getSlug())));
        }
        
        if ($request->getMethod() == 'POST') 
        {
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->request->get('category'));
            
            $item->setName($request->request->get('name'));
            $item->setCategory($category);
            
            if(!$request->get('newCategory') && !$category)
            {
                $this->get('session')->getFlashBag()->add('error', 'No existe la categoria');
            }
            else
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
                    $category->setDescription('');
                }
                
                $category->setLastchange(new \DateTime('now'));
                $em->persist($category); 
                $item->setCategory($category);
                                  
                
                $em->persist($item);
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('success', 'Carpeta modificada correctamente');
                return new RedirectResponse($this->generateUrl('ColectaFolderView', array('slug' => $item->getSlug())));     
            }
        }
        
        return $this->render('ColectaFilesBundle:Folder:edit.html.twig', array('item' => $item));
    }
    public function formlistAction($selected, $firstwrite)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $this->get('session')->getFlashBag()->add('error', 'Debes iniciar sesión');
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
                
                $folder->setCategory($em->getRepository('ColectaItemBundle:Category')->findOneById(1));
                $folder->setAuthor($user);
                
                
                $em->persist($folder);
                $em->flush();
                
                $selected = $folder->getId();
            }
        }
        
        $folders = $em->createQuery("SELECT f FROM ColectaFilesBundle:Folder f WHERE f.author = :user OR (f.public = 1 AND f.personal = 0 AND f.draft = 0) ORDER BY f.date DESC")->setParameter('user',$user->getId())->getResult();
        
        return $this->render('ColectaFilesBundle:Folder:folderselect.html.twig', array('folders'=>$folders, 'selected'=>$selected, 'firstwrite'=>$firstwrite));
    }
    
    public function chooseAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $this->get('session')->getFlashBag()->add('error', 'Debes iniciar sesión');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        //does the user have a personal folder?
        $personal = $em->getRepository('ColectaFilesBundle:Folder')->findOneBy(array('personal'=>1,'author'=>$user));
            
        if(!$personal)
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
        }
        
        $folders = $em->getRepository('ColectaFilesBundle:Folder')->findBy(array(), array('date'=>'DESC'));
        
        return $this->render('ColectaFilesBundle:Folder:choose.html.twig', array('folders'=>$folders));
    }
}
