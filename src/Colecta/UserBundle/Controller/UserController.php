<?php

namespace Colecta\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\UserBundle\Entity\User;


class UserController extends Controller
{
    private $ipp = 10; //Items per page
    
    /*
        View the public profile
    */
    public function profileAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $user = $em->getRepository('ColectaUserBundle:User')->find($id);
        //Get ALL the items that are not drafts
        $page = 0;
        $query = $em->createQuery(
            "SELECT i FROM ColectaItemBundle:Item i WHERE i.author = $id AND i.draft = 0 AND i NOT INSTANCE OF Colecta\FilesBundle\Entity\File ORDER BY i.lastInteraction DESC"
        )->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1);
        $items = $query->getResult();
        
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
        
        $salt = $user->getSalt();
        $thecode = substr(md5($salt.$this->container->getParameter('secret')),5,18);
        
        return $this->render('ColectaUserBundle:User:profile.html.twig', array('user' => $user, 'items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1), 'thecode' => $thecode));
    }
    public function itemsAction($id, $page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $em->getRepository('ColectaUserBundle:User')->find($id);
        
        //Get ALL the items that are not drafts
        $query = $em->createQuery(
            "SELECT i FROM ColectaItemBundle:Item i WHERE i.author = $id AND i.draft = 0 AND i NOT INSTANCE OF Colecta\FilesBundle\Entity\File ORDER BY i.lastInteraction DESC"
        )->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1);
        $items = $query->getResult();
        
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
        
        return $this->render('ColectaUserBundle:User:items.html.twig', array('user' => $user, 'items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    
    public function assistancesAction($id)
    {
        return $this->assistancesYearAction($id, date('Y'));
    }
    
    public function assistancesYearAction($id, $year)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('ColectaUserBundle:User')->find($id);
        
        $year = min( intval(date('Y')), max( 1990, intval($year) ) );
        
        $assistances = $em->createQuery('SELECT a FROM ColectaActivityBundle:Event e, ColectaActivityBundle:EventAssistance a WHERE a.event = e AND a.user = :user AND a.confirmed = 1 AND e.dateend >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$year.'-12-31 23:59:59\' ORDER BY e.dateini ASC')->setParameter('user',$user)->getResult();
        
        $pointsRequest = $em->createQuery('SELECT p FROM ColectaUserBundle:Points p, ColectaActivityBundle:Event e WHERE p.item = e AND p.user = :user AND  e.dateini >= \''.$year.'-01-01 00:00:00\' AND e.dateini <= \''.$year.'-12-31 23:59:59\'')->setParameter('user',$user)->getResult();
        
        $points = array();
        foreach($pointsRequest as $p)
        {
            $points[$p->getItem()->getId()] = $p;
        }
        
        $stmt = $em  
               ->getConnection()  
               ->prepare('SELECT DISTINCT(YEAR(e.dateini)) AS year FROM Event e INNER JOIN EventAssistance a ON e.id = a.event_id WHERE e.id = a.event_id AND a.user_id = :user_id AND e.dateend <= \''.date('Y').'-12-31 00:00:00\' ORDER BY e.dateini ASC');
               
        $stmt->bindValue('user_id', $user->getId());  
        $stmt->execute();  
        $years = $stmt->fetchAll();
        
        return $this->render('ColectaUserBundle:User:assistances.html.twig', array('user'=>$user, 'assistances'=>$assistances, 'points'=>$points, 'year'=>$year, 'years'=>$years));
    }
    
    public function editProfileAction() 
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Error, debes iniciar sesion'
            );
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $oldpass = $user->getPass();
        
        $form = $this->createFormBuilder($user)
            ->add('name',   'text',     array('label'=>'Nombre:',       'required'=>true))
            ->add('mail',   'email',    array('label'=>'Email:',        'required'=>true))
            //->add('pass',   'password', array('label'=>'Contraseña:',   'required'=>false))
            ->add('pass', 'repeated', array(
                                                    'type' => 'password',
                                                    'attr' => array(
                                                         'autocomplete' => 'off',
                                                     ),
                                                    'invalid_message' => 'Las contraseñas deben coincidir.',
                                                    'required' => false,
                                                    'first_options'  => array('label' => 'Nueva Contraseña'),
                                                    'second_options' => array('label' => 'Repite Contraseña'),
                                                    'first_name'  => 'pass1', // form.userPass.pass1
                                                    'second_name' => 'pass2' // form.userPass.pass2
                                                    ))
            ->add('file',   'file',     array('label'=>'Avatar:',       'required'=>false))
            ->getForm()
        ;
        
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                
                if(!$user->getPass()) 
                {
                    $user->setPass($oldpass);
                }
                else
                {
                    //Set new password
                    $encoder = $this->get('security.encoder_factory')
                                    ->getEncoder($user); 
                    $user->setSalt(md5(time()));
                    $encodedpass = $encoder->encodePassword( $user->getPassword(), $user->getSalt()); 
                    $user->setPass($encodedpass);
                    
                    $token = new UsernamePasswordToken($user, null, 'main', array('ROLE_USER'));
                    $this->get('security.context')->setToken($token);
                } 
                
                //Upload avatar
                $user->upload();
                
                
                $em = $this->getDoctrine()->getManager();
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
                
                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Datos modificados correctamente'
                );
                $this->redirect($this->generateUrl('userEditProfile'));
            }
            else
            {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'Ha ocurrido un error y no se han guardado los cambios'
                );
            }
        }
        
        return $this->render('ColectaUserBundle:User:editProfile.html.twig', array('form' => $form->createView()));
    }
    
    public function ajaxsearchAction()
    {
        $this->get('request')->setRequestFormat('json');
        
        $search = trim($this->get('request')->query->get('search'));
        
        $em = $this->getDoctrine()->getManager();
        
        $users = $em->createQuery("SELECT u FROM ColectaUserBundle:User u WHERE u.name LIKE :search AND u.role != 3")->setParameters(array('search'=>'%'.$search.'%'))->setMaxResults(12)->getResult();
        
        $response = new Response($this->renderView('ColectaUserBundle:User:users.json.twig', array('users' => $users)),200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function ajaxUserListAction()
    {
        $this->get('request')->setRequestFormat('json');
        
        $em = $this->getDoctrine()->getManager();
        
        $users = $em->getRepository('ColectaUserBundle:User')->findBy(array(), array('name'=>'ASC'));
        
        $response = new Response($this->renderView('ColectaUserBundle:User:usersDatum.json.twig', array('users' => $users)),200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function avatarAction($uid,$width,$height)
    {
        $this->get('request')->setRequestFormat('image');
        
        $cachePath = __DIR__ . '/../../../../app/cache/prod/images/avatar-' . $uid . '_' . $width . 'x' . $height ;
        
        $response = new Response();
        
        if(@filemtime($cachePath))
        {
            $response->setLastModified(new \DateTime(date("Y-m-d\TH:i:sP",filemtime($cachePath))));
        }
        else
        {
            $response->setLastModified(new \DateTime('now'));
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
            
            $user = $em->getRepository('ColectaUserBundle:User')->find($uid);
            
            if(!$user)
            {
                throw $this->createNotFoundException();
            }
            
            $imagefile = __DIR__ . '/../../../../web' . $user->getUploadDir() . '/' .$user->getAvatar();
            
            if($user->getAvatar() == '' || !file_exists($imagefile))
            {
                $imagefile = __DIR__ . '/../Resources/views/User/anonymous.png';
            }
            
            $image = new \Imagick($imagefile);
            
            $format = $image->getImageFormat();
            if ($format == 'GIF') 
            {
                //$image = $image->coalesceImages();

                foreach ($image as $frame) 
                {
                    $frame->scaleImage($width, $height, true);
                }
                
                //$image = $image->deconstructImages(); 
                $image->writeImages($cachePath, true); 
                
                $image = file_get_contents($cachePath);
            }
            else
            {
                $image->setImageResolution(72,72); 
                $image->cropThumbnailImage($width, $height);
                $image->setImagePage(0, 0, 0, 0);
                $image->normalizeImage();
                //fill out the cache
                file_put_contents($cachePath, $image);
            }
            
            $response->setStatusCode(200);
            $response->setContent($image);
            $response->headers->set('Content-Type', mime_content_type( $imagefile ));
            
            return $response;
        }
    }
}
