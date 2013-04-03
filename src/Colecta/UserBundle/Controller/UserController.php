<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\UserBundle\Entity\User;


class UserController extends Controller
{
    /*
        View the public profile
    */
    public function profileAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $user = $em->getRepository('ColectaUserBundle:User')->find($id);
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('author'=>$id), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaUserBundle:User:profile.html.twig', array('user' => $user, 'items' => $items));
    }
    
    public function assistancesAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('ColectaUserBundle:User')->find($id);
        
        $assistances = $em->createQuery("SELECT a FROM ColectaActivityBundle:EventAssistance a, ColectaActivityBundle:Event e WHERE e = a.event AND a.user = :user AND a.confirmed = true ORDER BY e.dateini DESC")->setParameters(array('user'=>$user->getId()))->setMaxResults(100)->getResult();
        
        return $this->render('ColectaUserBundle:User:assistances.html.twig', array('user' => $user, 'assistances' => $assistances));
    }
    
    public function editProfileAction() 
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $oldpass = $user->getPass();
        
        $form = $this->createFormBuilder($user)
            ->add('name',   'text',     array('label'=>'Nombre:',       'required'=>true))
            ->add('mail',   'email',    array('label'=>'Email:',        'required'=>true))
            //->add('pass',   'password', array('label'=>'Contraseña:',   'required'=>false))
            ->add('pass', 'repeated', array(
                                                    'type' => 'password',
                                                    'invalid_message' => 'Las contraseñas deben coincidir.',
                                                    'required' => false,
                                                    'first_options'  => array('label' => 'Nueva Contraseña'),
                                                    'second_options' => array('label' => 'Repite Contraseña')))
            ->add('file',   'file',     array('label'=>'Avatar:',       'required'=>false))
            ->getForm()
        ;
    
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                
                if(!$user->getPass()) 
                {
                    $user->setPass($oldpass);
                }
                
                $em = $this->getDoctrine()->getEntityManager();
                
                $user->upload();
            
                $em->persist($user);
                $em->flush();
                
                //Delete avatar cache
                $cachePath = __DIR__ . '/../../../../app/cache/prod/images';
                $uid = $user->getId();
                
                if ($handle = opendir($cachePath)) 
                {
                    while (false !== ($file = readdir($handle))) 
                    {
                        if(preg_match("#avatar-".$uid."_.*#", $file))
                        {
                            unlink($cachePath.'/'.$file);
                        }
                    }
                    closedir($handle);
                }
            
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
    
    public function avatarAction($uid,$width,$height)
    {
        $cachePath = __DIR__ . '/../../../../app/cache/prod/images/avatar-' . $uid . '_' . $width . 'x' . $height ;
        
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
            
            $user = $em->getRepository('ColectaUserBundle:User')->find($uid);
            
            if(!$user)
            {
                throw $this->createNotFoundException('El archivo no existe');
            }
            
            $imagefile = __DIR__ . '/../../../../web' . $user->getUploadDir() . '/' .$user->getAvatar();
            
            $image = new \Imagick($imagefile);
            $image->setImageResolution(72,72); 
            $image->scaleImage($width, $height, true);
            $image->setImagePage(0, 0, 0, 0);
            
            //fill out the cache
            file_put_contents($cachePath, $image);
            
            $response = new Response();
            
            $response->setStatusCode(200);
            $response->setContent($image);
            $response->headers->set('Content-Type', mime_content_type( $imagefile ));
            
            return $response;
        }
    }
}
