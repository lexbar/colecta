<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\UserBundle\Entity\User;


class UserController extends Controller
{
    
    public function profileAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $user = $em->getRepository('ColectaUserBundle:User')->find($id);
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('author'=>$id), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaUserBundle:User:profile.html.twig', array('user' => $user, 'items' => $items));
    }
    
    public function editProfileAction() 
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $form = $this->createFormBuilder($user)
            ->add('name')
            ->add('mail')
            ->add('pass')
            ->add('file')
            ->getForm()
        ;
    
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
            
                $user->upload();
            
                $em->persist($user);
                $em->flush();
            
                $this->redirect($this->generateUrl('userEditProfile'));
            }
        }
        
        return $this->render('ColectaUserBundle:User:editProfile.html.twig', array('form' => $form->createView()));
    }
    
    public function ajaxsearchAction()
    {
        $search = trim($this->get('request')->query->get('search'));
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $users = $em->createQuery("SELECT u FROM ColectaUserBundle:User u WHERE u.name LIKE :search")->setParameters(array('search'=>'%'.$search.'%'))->setMaxResults(12)->getResult();
        
        $response = new Response($this->renderView('ColectaUserBundle:User:users.json.twig', array('users' => $users)),200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
