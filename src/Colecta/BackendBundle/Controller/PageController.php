<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\SiteBundle\Entity\Page;

class PageController extends Controller
{
    public function indexAction()
    {
        // SECURITY 
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigSettings())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        //END SECURITY
                
        if ($this->get('request')->getMethod() == 'POST') 
        {   
            $request = $this->get('request')->request;
                       
            if(0)
            {
                return new RedirectResponse($this->generateUrl('ColectaBackendSettingsIndex'));
            }
            
            if(1)
            {
                $this->get('session')->getFlashBag()->add('error', 'No se ha podido guardar correctamente la configuración.');
            }
        }
        
        $pagesNavigation = $em->getRepository('ColectaSiteBundle:Page')->findBy(array('sidebarShow'=>true),array('sidebarOrder'=>'ASC'));
        $pagesOther = $em->getRepository('ColectaSiteBundle:Page')->findBy(array('sidebarShow'=>false),array('name'=>'ASC'));
        
        //Combine both
        $pages = array_merge($pagesNavigation, $pagesOther);
        
        return $this->render('ColectaBackendBundle:Page:index.html.twig', array('pages'=>$pages));
    }
    
    public function newPageAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user || !$user->getRole()->getSiteConfigPages())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        // New page handler
        $page = new Page();
        
        $roles = $em->getRepository('ColectaUserBundle:Role')->findAll();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request')->request;
            
            if($request->get('pageName'))
            {
                $page->setName($request->get('pageName'));
                //Slug generate
                    
                $slug = $page->generateSlug();
                    
                $n = 2;
                
                while($em->getRepository('ColectaSiteBundle:Page')->findOneBySlug($slug)) 
                {
                    if($n > 2)
                    {
                        $subtract = $num_length = strlen((string)$n) + 1 ;
                        $slug = substr($slug,0,-$subtract);
                    }
                    
                    $slug .= '-'.$n;
                    
                    $n++;
                }
                $page->setSlug($slug);
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'Debes escribir un título para la página.');
            }
            
            $page->setText($request->get('pageText'));
            
            if($request->get('pageIcon'))
            {
                $page->setIcon($request->get('pageIcon'));
            }
            else
            {
                $page->setIcon('');
            }
            
            $page->setDraft(0); // TODO: MANAGE DRAFTS !!
            
            $page->setAuthor($user);
            $page->setSidebarShow($request->get('pageSidebarShow') == 'on' ? true : false);
            
            //Sidebar order
            $pagesSidebar = $em->getRepository('ColectaSiteBundle:Page')->findBy(array('sidebarShow'=>true),array('sidebarOrder'=>'DESC'));
            if(count($pagesSidebar) > 0)
            {
                $page->setSidebarOrder(intval($pagesSidebar[0]->getSidebarOrder()) + 1);
            }
            else
            {  
                $page->setSidebarOrder(1);
            }
            
            //Set target Roles
            $target_roles = array(); //is a colection of ids for Role entities
            
            //Anonnymous role
            if( $request->get('pageTargetRole0') == 'on' )
            {
                array_push($target_roles, 0);
            }
            
            foreach( $roles as $role )
            {
                if( $request->get('pageTargetRole' . $role->getId()) == 'on' )
                {
                    array_push($target_roles, $role->getId());
                }
            }
            
            $page->setTargetRoles($target_roles);
            
             //CONTACT FORM CAPABILITIES
            $contactForm = $request->get('pageContact') == 'on' ? true : false;
            
            if($contactForm)
            {
                $field = 0;
                $contactDataFields = array();
                while($request->get('contactFormField'. $field .'Type'))
                {
                    $contactDataFields[$field]['type'] = $request->get('contactFormField'. $field .'Type');
                    $contactDataFields[$field]['title'] = $request->get('contactFormField'. $field .'Title');
                    $contactDataFields[$field]['value'] = $request->get('contactFormField'. $field .'Value');
                    $contactDataFields[$field]['help'] = $request->get('contactFormField'. $field .'Help');
                    
                    $field++;
                }
                
                $page->setContactData( array('fields' => $contactDataFields, 'email' => $request->get('contactFormEmail')) );
                
                $page->setContact(true);
            }
            else
            {
                $page->setContact(false);
                $page->setContactData(array());
            }
            
            if($page->getName())
            {
                $em->persist($page); 
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('success', 'Página creada correctamente');
                
                return new RedirectResponse($this->generateUrl('ColectaBackendPage', array('page_id'=>$page->getId()))); //Page edit page
            }
        }
        
        return $this->render('ColectaBackendBundle:Page:pageEdit.html.twig', array('page'=>$page, 'roles'=>$roles));
    }
    
    public function editPageAction($page_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigPages())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        // Page handler
        $page = $em->getRepository('ColectaSiteBundle:Page')->findOneById($page_id);
        
        if(! $page)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe la página.');
            return new RedirectResponse($this->generateUrl('ColectaBackendPageIndex'));
        }
        
        $roles = $em->getRepository('ColectaUserBundle:Role')->findAll();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request')->request;
            
            if($request->get('pageName'))
            {
                if($page->getName() != $request->get('pageName')) // If name has changed
                {
                    $page->setName($request->get('pageName'));
                    //Slug generate
                        
                    $slug = $page->generateSlug();
                        
                    $n = 2;
                    
                    while($em->getRepository('ColectaSiteBundle:Page')->findOneBySlug($slug)) 
                    {
                        if($n > 2)
                        {
                            $subtract = $num_length = strlen((string)$n) + 1 ;
                            $slug = substr($slug,0,-$subtract);
                        }
                        
                        $slug .= '-'.$n;
                        
                        $n++;
                    }
                    $page->setSlug($slug);   
                }
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'Debes escribir un título para la página.');
            }
            
            $page->setText($request->get('pageText'));
            
            if($request->get('pageIcon'))
            {
                $page->setIcon($request->get('pageIcon'));
            }
            else
            {
                $page->setIcon('');
            }
            
            $page->setDraft(0); // TODO: MANAGE DRAFTS !!
            
            $page->setAuthor($user);
            
            
            //Sidebar order
            $sidebarShow = $request->get('pageSidebarShow') == 'on' ? true : false;
            
            if($sidebarShow)
            {
                if(!$page->getSidebarShow()) //if it wasn't for sidebar but now it is...
                {
                    $pagesSidebar = $em->getRepository('ColectaSiteBundle:Page')->findBy(array('sidebarShow'=>true),array('sidebarOrder'=>'DESC'));
                    
                    if(count($pagesSidebar) > 0)
                    {
                        $page->setSidebarOrder(intval($pagesSidebar[0]->getSidebarOrder()) + 1);
                    }
                    else
                    {  
                        $page->setSidebarOrder(1);
                    }
                    
                    $page->setSidebarShow(1);
                }
            }
            else
            {
                $page->setSidebarShow(0);
            }
            
            // TARGET ROLES
            $target_roles = array(); //is a colection of ids for Role entities
            
            //Anonnymous role
            if( $request->get('pageTargetRole0') == 'on' )
            {
                array_push($target_roles, 0);
            }
            
            foreach( $roles as $role )
            {
                if( $request->get('pageTargetRole' . $role->getId()) == 'on' )
                {
                    array_push($target_roles, $role->getId());
                }
            }
            
            $page->setTargetRoles($target_roles);
            
            //CONTACT FORM CAPABILITIES
            $contactForm = $request->get('pageContact') == 'on' ? true : false;
            
            if($contactForm)
            {
                $field = 0;
                $contactDataFields = array();
                while($request->get('contactFormField'. $field .'Type'))
                {
                    $contactDataFields[$field]['type'] = $request->get('contactFormField'. $field .'Type');
                    $contactDataFields[$field]['title'] = $request->get('contactFormField'. $field .'Title');
                    $contactDataFields[$field]['value'] = $request->get('contactFormField'. $field .'Value');
                    $contactDataFields[$field]['help'] = $request->get('contactFormField'. $field .'Help');
                    
                    $field++;
                }
                
                $page->setContactData( array('fields' => $contactDataFields, 'email' => $request->get('contactFormEmail')) );
                
                $page->setContact(true);
            }
            else
            {
                $page->setContact(false);
                $page->setContactData(array());
            }
            
            if($page->getName())
            {
                $em->persist($page); 
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('success', 'Cambios guardados');
                
                return new RedirectResponse($this->generateUrl('ColectaBackendPage', array('page_id'=>$page->getId()))); //Page edit page
            }
        }
        
        return $this->render('ColectaBackendBundle:Page:pageEdit.html.twig', array('page'=>$page, 'roles'=>$roles));
    }
    
    public function deletePageAction($page_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigPages())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        // Page handler
        $page = $em->getRepository('ColectaSiteBundle:Page')->findOneById($page_id);
        
        if(! $page)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe la página.');
            
        }
        else
        {
            $em->remove($page); 
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Página eliminada correctamente'); 
        }
        
        return new RedirectResponse($this->generateUrl('ColectaBackendPageIndex'));
    }
    
    public function movePageUpAction($page_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigPages())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        // Page handler
        $page = $em->getRepository('ColectaSiteBundle:Page')->findOneById($page_id);
        
        if(! $page)
        {
            $this->get('session')->getFlashBag()->add('error', 'La página no existe.');
        }
        elseif(!$page->getSidebarShow())
        {
            $this->get('session')->getFlashBag()->add('error', 'La página no es visible en la barra de navegación.');
        }
        else
        {
            $pagesSidebar = $em->createQuery(
                'SELECT p
                FROM ColectaSiteBundle:Page p
                WHERE p.sidebarShow = 1 AND p.sidebarOrder <= :order AND p.id != :id
                ORDER BY p.sidebarOrder DESC'
            )->setParameter('id', $page->getId())->setParameter('order', $page->getSidebarOrder())->setMaxResults(1)->getResult();
            
            if(count($pagesSidebar) > 0)
            {
                //Swap orders
                $swapPage = $pagesSidebar[0];
                
                $page->setSidebarOrder($swapPage->getSidebarOrder());
                $swapPage->setSidebarOrder($page->getSidebarOrder() + 1);
                
                
                $em->persist($page);
                $em->persist($swapPage);
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('success', 'Orden de página modificado correctamente.'); 
            }
        }
        
        return new RedirectResponse($this->generateUrl('ColectaBackendPageIndex'));
    }
    
    public function movePageDownAction($page_id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigPages())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        // Page handler
        $page = $em->getRepository('ColectaSiteBundle:Page')->findOneById($page_id);
        
        if(! $page)
        {
            $this->get('session')->getFlashBag()->add('error', 'La página no existe.');
        }
        elseif(!$page->getSidebarShow())
        {
            $this->get('session')->getFlashBag()->add('error', 'La página no es visible en la barra de navegación.');
        }
        else
        {
            $pagesSidebar = $em->createQuery(
                'SELECT p
                FROM ColectaSiteBundle:Page p
                WHERE p.sidebarShow = 1 AND p.sidebarOrder >= :order AND p.id != :id
                ORDER BY p.sidebarOrder ASC'
            )->setParameter('id', $page->getId())->setParameter('order', $page->getSidebarOrder())->setMaxResults(1)->getResult();
            
            if(count($pagesSidebar) > 0)
            {
                //Swap orders
                $swapPage = $pagesSidebar[0];
                
                $page->setSidebarOrder($swapPage->getSidebarOrder());
                $swapPage->setSidebarOrder($page->getSidebarOrder() - 1);
                
                $em->persist($page);
                $em->persist($swapPage);
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('success', 'Orden de página modificado correctamente.'); 
            }
        }
        
        return new RedirectResponse($this->generateUrl('ColectaBackendPageIndex'));
    }
}