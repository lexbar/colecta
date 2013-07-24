<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Colecta\ActivityBundle\Form\Frontend\RouteType;
use Colecta\ActivityBundle\Entity\Route;
use Colecta\ActivityBundle\Entity\RouteTrackpoint;
use Colecta\ActivityBundle\Entity\Place;
use Colecta\ItemBundle\Entity\Relation;
use Colecta\ItemBundle\Entity\Category;
use Colecta\UserBundle\Entity\Notification;


class RouteController extends Controller
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
        $items = $em->getRepository('ColectaActivityBundle:Route')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
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
        
        return $this->render('ColectaActivityBundle:Route:index.html.twig', array('items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Route')->findOneBySlug($slug);
        
        return $this->render('ColectaActivityBundle:Route:full.html.twig', array('item' => $item));
    }
    public function mapAction($id)
    {
        $cachePath = __DIR__ . '/../../../../app/cache/prod/images/maps/' . $id ;
        
        if(file_exists($cachePath))
        {
            $image = file_get_contents($cachePath);
        }
        else
        {
            $em = $this->getDoctrine()->getEntityManager();
            
            
            $item = $em->getRepository('ColectaActivityBundle:Route')->findOneById($id);
            $itemtrack = $item->getTrackpoints();
            
            $n = count($itemtrack);
            if($n > 0) 
            {
                $url = "http://maps.google.com/maps/api/staticmap?size=150x100&maptype=terrain&sensor=false&path=color:0xff0000|weight:2";
                $step = floor($n / 60);
                for($i = 0; $i < $n; $i += $step)
                {
                    $url .= '|'. $itemtrack[$i]->getLatitude() .','. $itemtrack[$i]->getLongitude();
                }
                
                $image = getContent($url);
                
                file_put_contents($cachePath, $image);
            }
            else 
            {
                return new Response('Page not found.', 404);
            }
        }
        
        $response = new Response($image);
        $response->headers->set('Content-Type', 'image/png');
        return $response;
    }
    public function newAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaActivityBundle:Route:new.html.twig');
    }
    public function uploadAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.') 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        else
        {
            if (!$_FILES['file'] || ($file = new UploadedFile($_FILES['file']['tmp_name'],$_FILES['file']['name'])) === null) 
            {
                $this->get('session')->setFlash('error', 'Ha ocurrido un error al subir el archivo.');
            }
            
            $extension = $this->extension($file->getClientOriginalName());
            
            if(!$extension) //Extension not accepted
            {
                $this->get('session')->setFlash('error', 'El archivo no tiene una extensión correcta.');
            }
            else
            {
                $hashName = sha1($file->getClientOriginalName() . $user->getId() . mt_rand(0, 99999));
                $filename = $hashName . '.' . $extension;
                
                $rootdir = $cachePath = __DIR__ . '/../../../../app/cache/prod/files';
                
                $file->move($rootdir, $filename);
                unset($file);
                
                $fulltrack = $this->extractTrack($rootdir.'/'.$filename); //full track
                                
                if(!$fulltrack)
                {
                    $this->get('session')->setFlash('error', 'No se ha podido leer correctamente el archivo.');
                }
                else
                {
                    $item = new Route();
                    $form = $this->createForm(new RouteType(), $item);
                    
                    $points = count($fulltrack);
                    if($points > 500)
                    {
                        $track = array();
                        $cadence = ceil($points / 500);
                        
                        for($i = 0; $i < $points ; $i += $cadence)
                        {
                            $track[] = $fulltrack[$i];
                        }
                    }
                    else
                    {
                        $track = $fulltrack;
                    }
                    
                    $itemdata = $this->getRouteData($fulltrack);
                    
                    $categories = $this->getDoctrine()->getEntityManager()->getRepository('ColectaItemBundle:Category')->findAll();
                    
                    return $this->render('ColectaActivityBundle:Route:filldata.html.twig', array('filename' => $filename, 'track' => $track, 'trackdata' => $itemdata, 'form' => $form->createView(), 'categories' => $categories));
                }
            }
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaRouteIndex');
        }
        
        return new RedirectResponse($referer);
        
    }
    public function XHRUploadAction() 
    {
        //Single file upload from XHR form
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.') 
        {
            throw $this->createNotFoundException();
        }
        
        set_time_limit(60*5);

        //Only first file
        if(is_array($_FILES['file']['tmp_name']))
        {
            $file = new UploadedFile($_FILES['file']['tmp_name'][0],$_FILES['file']['name'][0],$_FILES['file']['type'][0],$_FILES['file']['size'][0],$_FILES['file']['error'][0]);
        }
        else
        {
            $file = new UploadedFile($_FILES['file']['tmp_name'],$_FILES['file']['name'],$_FILES['file']['type'],$_FILES['file']['size'],$_FILES['file']['error']);
        } 
        
        $cachePath = __DIR__ . '/../../../../app/cache/prod/files' ;
        $filename = 'xhr-' . md5($file->getClientOriginalName() . $_FILES['file']['tmp_name']);
        
        $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        
        $file->move($cachePath, $filename.'.'.$extension);
        
        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent($filename.'.'.$extension);
        $response->headers->set('Content-Type', 'text/plain');
        
        return $response;
    }
    public function XHRPreviewAction($token)
    {        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $response = new Response();
        $response->setStatusCode(200);
        
        if($user == 'anon.') 
        {
            $response->setContent('<strong>Ha ocurrido un error, tienes que estar logueado.</strong>');
        
            return $response;
        }
        else
        {
            $cachePath = __DIR__ . '/../../../../app/cache/prod/files' ;
            $token = $this->safeToken($token);
            
            $extension = $this->extension($token);
            
            if(!$extension) //Extension not accepted
            {
                $response->setContent('<strong>El archivo no tiene una extensión correcta.</strong>');
                
                if(file_exists($cachePath.'/'.$token))
                {
                    unlink($cachePath.'/'.$token);
                }
                
                return $response;
            }
            else
            {                
                $fulltrack = $this->extractTrack($cachePath.'/'.$token); //full track   
                if(!$fulltrack)
                {
                    $response->setContent('<strong>No se ha podido leer correctamente el archivo.</strong>');
                    
                    if(file_exists($cachePath.'/'.$token))
                    {
                        unlink($cachePath.'/'.$token);
                    }
                
                    return $response;
                }
                else
                {
                    $item = new Route();
                    $form = $this->createForm(new RouteType(), $item);
                    
                    $points = count($fulltrack);
                    if($points > 500)
                    {
                        $track = array();
                        $cadence = ceil($points / 500);
                        
                        for($i = 0; $i < $points ; $i += $cadence)
                        {
                            $track[] = $fulltrack[$i];
                        }
                    }
                    else
                    {
                        $track = $fulltrack;
                    }
                    
                    $itemdata = $this->getRouteData($fulltrack);
                    
                    $guessname = $this->guessName($fulltrack[0]['latitude'],$fulltrack[0]['longitude']);
                    
                    return $this->render('ColectaActivityBundle:Route:detailsform.html.twig', array('guessname'=>$guessname, 'filename' => $token, 'track' => $track, 'trackdata' => $itemdata, 'form' => $form->createView()));
                }
            }
        }
        
    }
    public function createAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request');
        $post = $request->request;
        
        if($this->get('request')->getMethod() == 'POST')
        {
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
        
            if(!$user) 
            {
                $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            }
            elseif(!$category && !$request->get('newCategory'))
            {
                $this->get('session')->setFlash('error', 'No existe la categoria');
            }
            else
            {
                $item = new Route();
                $form = $this->createForm(new RouteType(), $item);
                $form->bindRequest($request);
                
                if ($form->isValid()) 
                {
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
                            
                            $catSlug .= '-'.$n;
                            
                            $n++;
                        }
                    
                        $category->setSlug($catSlug);
                        $category->setDescription('');
                    }
                    
                    $category->setLastchange(new \DateTime('now'));
                    $em->persist($category); 
                    
                    $item->setCategory($category);
                    $item->setAuthor($user);
                    
                    //if comes from itemSubmit i have to fill name and description
                    if($request->get('name'))
                    {
                        $item->setName($request->get('name'));
                    }
                    
                    if($request->get('description'))
                    {
                        $item->setDescription($request->get('description'));
                    }
                    elseif(!$item->getDescription())
                    {
                        $item->setDescription('');
                    }
                    
                    
                    //Move file out of cache to accesible folder
                    $cachePath = __DIR__ . '/../../../../app/cache/prod/files' ;
                    $rootdir = $this->getUploadDir();
                    
                    $filename = $this->safeToken($post->get('filename'));
                    
                    if(copy($cachePath.'/'.$filename, $rootdir.'/'.$filename))
                    {
                        unlink($cachePath.'/'.$filename);
                    }
                    
                    $fulltrack = $this->extractTrack($rootdir.'/'.$filename, 10000); //the track, limited to 10000 points for performance reasons
                    
                    if(!$item->getName())
                    {
                        $item->setName($this->guessName($fulltrack[0]['latitude'],$fulltrack[0]['longitude']));
                    }
                    
                    //Slug generate
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
                    
                    $item->summarize(strval($item->getDescription()));
                    
                    $item->setAllowComments(true);
                    $item->setDraft(false);
                    $item->setPart(false);
                    
                    $item->setActivity(null);
                    $item->setDifficulty($post->get('difficulty'));
                    
                    $time = intval($post->get('days')) * 24 * 60 * 60 + intval($post->get('hours')) * 60 * 60 + intval($post->get('minutes')) * 60;
                    $item->setTime($time);
                    
                    $item->setIsloop(false);
                    $item->setIBP('');
                    $item->setIsloop(intval($post->get('isloop')));
                    
                    $item->setSourcefile($filename);
                    
                    if($post->get('attachTo'))
                    {
                        $itemRelated = $em->getRepository('ColectaItemBundle:Item')->findOneById($post->get('attachTo'));
                        $relation = new Relation();
                        $relation->setUser($user);
                        $relation->setItemto($itemRelated);
                        $relation->setItemfrom($item);
                        $relation->setText($itemRelated->getName());
                        
                        $em->persist($relation);
                        
                        $item->setPart(true);
                    }
                    
                    $em->persist($item); 
                    
                    //Save RouteTrackpooints
                    
                    foreach($fulltrack as $point)
                    {
                        $trackpoint = new RouteTrackpoint();
                        $trackpoint->setLatitude($point['latitude']);
                        $trackpoint->setLongitude($point['longitude']);
                        $trackpoint->setAltitude($point['altitude']);
                        $trackpoint->setDate($point['datetime']);
                        $trackpoint->setRoute($item);
                        
                        
                        $em->persist($trackpoint); 
                        unset($trackpoint);
                    }
                    
                    //Retrieve and save Places
                    $points = $this->extractPoints($rootdir.'/'.$filename); //the waypoints
                    
                    foreach($points as $point)
                    {
                        $place = new Place();
                        $place->setLatitude($point['latitude']);
                        $place->setLongitude($point['longitude']);
                        if(!empty($point['name']))
                        {
                            $place->setName($point['name']);
                        }
                        else
                        {
                            $place->setName($item->getName());
                        }
                        $place->setDescription($point['description']);
                        $place->summarize($place->getDescription());
                        $place->setTagwords($item->getTagwords());
                        $place->setAuthor($user);
                        $place->setCategory($category);
                        $place->setAllowComments(true);
                        $place->setDraft(true);
                        $place->setPart(true);
                        
                        //Slug generate
                        $slug = $place->generateSlug();
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
                        $place->setSlug($slug);
                        
                        $relation = new Relation();
                        $relation->setUser($user);
                        $relation->setItemfrom($item);
                        $relation->setItemTo($place);
                        $relation->setText($place->getName());
                        $em->persist($relation); 
                        
                        $place->addRelatedto($relation);
                        
                        $em->persist($place); 
                    }
                    
                    $em->flush();
                    
                    // Update all categories. 
                    // This is done this way because I'm lazy and so that every time an item is created or modified consistency is granted.
                
                    $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
                    
                    return new RedirectResponse($this->generateUrl('ColectaRouteView', array('slug' => $item->getSlug())));
                }
                else
                {
                    $this->get('session')->setFlash('error', 'Revisa los campos');
                    
                    $filename = $post->get('filename');
                    $rootdir = $this->getUploadDir();
                    
                    $track = $this->extractTrack($rootdir.'/'.$filename, 500); //simplified to 500 points only
                    $fulltrack = $this->extractTrack($rootdir.'/'.$filename); //full track
                    $itemdata = $this->getRouteData($fulltrack);
                    
                    $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
                    
                    return $this->render('ColectaActivityBundle:Route:filldata.html.twig', array('filename' => $filename, 'track' => $track, 'trackdata' => $itemdata, 'form' => $form->createView(), 'categories' => $categories));
                }
            }
        }
                
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaRouteIndex');
        }
        
        return new RedirectResponse($referer);
    }
    public function editAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request');
        $post = $request->request;
        
        $item = $em->getRepository('ColectaActivityBundle:Route')->findOneBySlug($slug);
        $form = $this->createForm(new RouteType(), $item);
        
        if($user == 'anon.' || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaRouteView', array('slug'=>$slug)));
        }
        
        if ($this->get('request')->getMethod() == 'POST') {
            $persist = true;
            
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($post->get('category'));
            
            if(!$category)
            {
                $this->get('session')->setFlash('error', 'No existe la categoria');
                $persist = false;
            }
            else
            {
                if($post->get('newCategory'))
                {
                    $category = new Category();
                    $category->setName($post->get('newCategory'));
                    
                    //Category Slug generate
                    $catSlug = $item->generateSlug($post->get('newCategory'));
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
                    
                    $em->persist($category); 
                }
                
                $item->setCategory($category);
            }
            
            $form->bindRequest($request);
            
            if(!$item->getDescription())
            {
                $this->get('session')->setFlash('error', 'No puedes dejar vacío el texto');
                $persist = false;
            }
            
            //File replacement
            if ($persist && isset($_FILES['file']) && $_FILES['file']['tmp_name'])
            {
                if(($file = new UploadedFile($_FILES['file']['tmp_name'],$_FILES['file']['name'])) === null)
                {
                    $this->get('session')->setFlash('error', 'No se ha podido subir el archivo.');
                    $persist = false;
                }
                else
                {
                    $extension = $this->extension($file->getClientOriginalName());
            
                    if(!$extension) //Extension not accepted
                    {
                        $this->get('session')->setFlash('error', 'El archivo no tiene una extensión correcta.');
                        $persist = false;
                    }
                    else
                    {
                        $hashName = sha1($file->getClientOriginalName() . $user->getId() . mt_rand(0, 99999));
                        $filename = $hashName . '.' . $extension;
                        
                        $rootdir = $this->getUploadDir();
                        
                        $file->move($rootdir, $filename);
                        unset($file);
                        
                        $track = $this->extractTrack($rootdir.'/'.$filename, 500); //simplified to 500 points only
                                        
                        if(!$track)
                        {
                            $this->get('session')->setFlash('error', 'No se ha podido leer correctamente el archivo.');
                            $persist = false;
                        }
                        else
                        {
                            $item->setSourcefile($filename);
                            $em->createQuery("DELETE ColectaActivityBundle:RouteTrackpoint t WHERE t.route = :route")->setParameter('route', $item)->execute();
                            
                            //New RouteTrackpooints                            
                            foreach($track as $point)
                            {
                                $trackpoint = new RouteTrackpoint();
                                $trackpoint->setLatitude($point['latitude']);
                                $trackpoint->setLongitude($point['longitude']);
                                $trackpoint->setAltitude($point['altitude']);
                                $trackpoint->setDate($point['datetime']);
                                $trackpoint->setRoute($item);
                                
                                
                                $em->persist($trackpoint); 
                                unset($trackpoint);
                            }
                            
                            //Delete avatar cache
                            $cachePath = __DIR__ . '/../../../../app/cache/prod/images/maps/';
                            $id = $item->getId();
                            
                            if ($handle = opendir($cachePath)) 
                            {
                                while (false !== ($file = readdir($handle))) 
                                {
                                    if(preg_match("#".$id.".*#", $file))
                                    {
                                        unlink($cachePath.'/'.$file);
                                    }
                                }
                                closedir($handle);
                            }
                        }
                    }

                }
            }
            
            $item->summarize($item->getDescription());
            $item->setDifficulty($post->get('difficulty'));
            $time = intval($post->get('days')) * 24 * 60 * 60 + intval($post->get('hours')) * 60 * 60 + intval($post->get('minutes')) * 60;
            $item->setTime($time);
            
            if($persist)
            {
                $em->persist($item); 
                $em->flush();
                $this->get('session')->setFlash('success', 'Modificado con éxito.');
                
                // Update all categories. 
                // This is done this way because I'm lazy and so that every time an item is created or modified consistency is granted.
            
                $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        return $this->render('ColectaActivityBundle:Route:edit.html.twig', array('item' => $item, 'categories'=>$categories, 'form' => $form->createView()));
    }
    
    public function deleteAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Route')->findOneBySlug($slug);
        
        if(!$item)
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        if($user == 'anon.' || !$item->canEdit($user)) 
        {
            return new RedirectResponse($this->generateUrl('ColectaPostView', array('slug'=>$slug)));
        }
        
        $name = $item->getName();
        
        $em->remove($item);
        $em->flush();
        
        $this->get('session')->setFlash('success', '"'.$name.'" ha sido eliminado.');
        
        return new RedirectResponse($this->generateUrl('ColectaDashboard'));
    }
    
    public function downloadAction($slug, $extension)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $item = $em->getRepository('ColectaActivityBundle:Route')->findOneBySlug($slug);
        
        $oformat = $this->acceptedExtensions($extension);
        
        if($item && $oformat)
        {
            $filepath = $this->getUploadDir() . '/' . $item->getSourcefile();
            $iformat = $this->acceptedExtensions($this->extension($item->getSourcefile()));
            $simplified = $this->get('request')->query->get('simplified');
            
            if($simplified && is_numeric($simplified))
            {
                $out = shell_exec("gpsbabel -t -i $iformat -f $filepath -x simplify,count=$simplified -o $oformat -F -");
            }
            else
            {
                $out = shell_exec("gpsbabel -t -i $iformat -f $filepath -o $oformat -F -");
            }
        
            $response = new Response($out);
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename=' . $item->getSlug() . '.' .$extension );
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            
            return $response;
        }
        else
        {
            throw $this->createNotFoundException('El formato '.$formato.' no existe.');
        }
    }
    public function getUploadDir($absolute = true)
    {
        $uploaddir = 'uploads/routes';
        if($absolute)
        {
            return __DIR__ . '/../../../../web/' . $uploaddir;
        }
        else
        {
            return $uploaddir;
        }
        
    }
    public function acceptedExtensions($extension = false)
    {
        $ae = array(
    		"trl" => "alantrl", //Alan Map500 tracklogs
    		"wpr" => "alanwpr", //Alan Map500 waypoints and routes
    		"cst" => "cst", //CarteSurTable data file 
    		"wpt" => "compegps", //CompeGPS data files
    		"trk" => "compegps", //CompeGPS data files
    		"rte" => "compegps", //CompeGPS data files
    		"gpb" => "axim_gpb", //Dell Axim Navigation System
    		"dat" => "destinator_trl", //Destinator TrackLogs
    		"g7t" => "g7towin", //G7ToWin data files
    		"gdb" => "gdb", //Garmin MapSource
    		"mps" => "mapsource", //Garmin MapSource
    		"crs" => "gtrnctr", //Garmin Training Center
    		"loc" => "geo", //Geocaching.com .loc
    		"ovl" => "ggv_ovl", //Geogrid-Viewer ascii overlay file
    		"log" => "ggv_log", //Geogrid-Viewer tracklogs
    		"kml" => "kml", //Google Earth (Keyhole) Markup Language
    		"trl" => "gnav_trl", //Google Navigator Tracklines
    		"trk" => "gopal", //GoPal GPS track log
    		"gpx" => "gpx", //GPX XML
    		"ht" => "humminbird_ht", //Humminbird tracks
    		"tk" => "kompass_tk", //Kompass (DAV) Track
    		"rte" => "nmn4", //Navigon Mobile Navigator
    		"bin" => "navitel_trk", //Navitel binary track
    		"plt" => "ozi", //OziExplorer
    		"itn" => "tomtom_itn", //TomTom Itineraries
    		"trl" => "dmtlog", //TrackLogs digital mapping
    		"tcx"=>"gtrnctr"
    	);
    	
    	if($extension)
    	{
    	   if(in_array($extension, array_keys($ae)))
    	   {
    	       return $ae[$extension];
    	   }
    	   else
    	   {
    	       return false;
    	   }
    	}
    	else
    	{
    	   return $ae;
    	}
    }
    
    public function extension($filename)
    {
        $accepted = array_keys($this->acceptedExtensions());
        
        $exploded = explode('.',$filename);
        
        if(!count($exploded))
        {
            return false;
        }
        
        $extension = strtolower(end($exploded));
        
        if(in_array($extension, $accepted))
        {
            return $extension;
        }
        else
        {
            return false;
        }
    }
    
    public function extractTrack($filepath, $simplified = false)
    {
        //Using gpsbabel we transform whatever format the file is to CVS
        $format = $this->acceptedExtensions($this->extension($filepath));
        if($simplified && is_numeric($simplified))
        {
            $csv = shell_exec("gpsbabel -t -i $format -f $filepath -x simplify,count=$simplified -o unicsv -F -");
        }
        else
        {
            $csv = shell_exec("gpsbabel -t -i $format -f $filepath -o unicsv -F -");
        }
        
        $lines = explode("\n",$csv);
        
        //Check for the order of values, set in first line
		$csvheader = explode(',',$lines[0]);
		
		if(count($csvheader)) {
			$i = 0;
			$latitude = $longitude = $name = $altitude = $date = $time = null;
			foreach($csvheader as $row) {
				switch(trim($row)) {
					case 'Latitude': $latitude = $i ; break;
					case 'Longitude': $longitude = $i ; break;
					case 'Name': $name = $i ; break;
					case 'Altitude': $altitude = $i ; break;
					case 'Date': $date = $i ; break;
					case 'Time': $time = $i ; break;
				}
				$i++;
			}
		} else {
			return null; //something went wrong
		}
		
		unset($lines[0]); //Delete the header
		
		if(count($lines) > 1) 
		{ // there must be at least 2 points to create a track
			$track = array();
			
			foreach($lines as $line) {
				$l = explode(',',$line);
				if(count($l) != count($csvheader)) break; //count of data in csv must match with the header line
				
				$trackpoint = array();
				
				$trackpoint['latitude'] = $l[$latitude];
				$trackpoint['longitude'] = $l[$longitude];
				if($altitude !== null) { $trackpoint['altitude'] = $l[$altitude];}
				else { $trackpoint['altitude'] = 0; }
				if($date !== null) { $trackpoint['datetime'] = safeDateTime($l[$date],$l[$time]); }
				else { $trackpoint['datetime'] = new \DateTime('today'); }
				
				$track[] = $trackpoint;
			}
			
			return $track;
		}
		
		return null;
    }
    
    function extractPoints($filepath) {
        //Using gpsbabel we transform whatever format the file is to CVS
        $format = $this->acceptedExtensions($this->extension($filepath));
        
        $csv = shell_exec("gpsbabel -w -i $format -f $filepath -o unicsv -F -");
        
        $lines = explode("\n",$csv);
        
        //Check for the order of values, set in first line
		$csvheader = explode(',',$lines[0]);
		
		if(count($csvheader)) {
			$i = 0;
			$latitude = $longitude = $name = $altitude = $date = $time = null;
			foreach($csvheader as $row) {
				switch(trim($row)) {
					case 'Latitude': $latitude = $i ; break;
					case 'Longitude': $longitude = $i ; break;
					case 'Name': $name = $i ; break;
					case 'Description': $description = $i ; break;
					case 'Altitude': $altitude = $i ; break;
					case 'Date': $date = $i ; break;
					case 'Time': $time = $i ; break;
				}
				$i++;
			}
		} else {
			return null; //something went wrong
		}
		
		unset($lines[0]); //Delete the header
		
		if(count($lines) > 0) 
		{
			$points = array();
			
			foreach($lines as $line) {
				$l = explode(',',$line);
				if(count($l) != count($csvheader)) break; //count of data in csv must match with the header line
				
				$point = array();
				
				$point['latitude'] = $l[$latitude];
				$point['longitude'] = $l[$longitude];
				$point['name'] = preg_replace('#^\"(.*)\"$#', '$1', $l[$name]);
				$point['description'] = preg_replace('#^\"(.*)\"$#', '$1', $l[$description]);
				if($altitude !== null) { $trackpoint['altitude'] = $l[$altitude];}
				else { $point['altitude'] = 0; }
				if($date !== null) { $point['datetime'] = safeDateTime($l[$date],$l[$time]); }
				else { $point['datetime'] = new \DateTime('today'); }
				
				$points[] = $point;
			}
			
			return $points;
		}
		
		return null;
	}
    
    public function getRouteData($track)
    {
        $distance = $upHill = $downHill = $maxSpeed = 0;
        $speeds = array();
                
        $amount = count($track);
        
        $minheight = $track[0]['altitude'];
        $maxheight = $track[0]['altitude'];
        
        for($i = 1; $i < $amount; $i++)
        {
            $j = $i - 1;
            
            // Distance
            $distance += distance(
                $track[$i]['latitude'], $track[$i]['longitude'], $track[$i]['altitude'],
                $track[$j]['latitude'], $track[$j]['longitude'], $track[$j]['altitude']
            );
            
            if($i >= 8 && $i % 4 == 0) //Every four points
            { 
                // Uphill and Downhill 
                $alt1 = median( $track[$i - 1]['altitude'] , $track[$i - 2]['altitude'], $track[$i - 3]['altitude'] , $track[$i - 4]['altitude'] );
	 		    $alt2 = median( $track[$i - 5]['altitude'] , $track[$i - 6]['altitude'], $track[$i - 7]['altitude'] , $track[$i - 8]['altitude'] );
	 		
                if($alt1 > $alt2 ) 
                {
                    $upHill += $alt1 - $alt2;
                }
                else
                {
                    $downHill += $alt2 - $alt1;
                }
                
                // Velocity
                $lapseTime = $track[$i]['datetime']->format('U') - $track[$i - 4]['datetime']->format('U');
                if($lapseTime)
                {
                    $lapseDistance = distance(
                        $track[$i]['latitude'], $track[$i]['longitude'], $track[$i]['altitude'],
                        $track[$i - 4]['latitude'], $track[$i - 4]['longitude'], $track[$i - 4]['altitude']);
                    
                    $speed = ( $lapseDistance / 1000 ) / ( ( $lapseTime ) / 60 / 60 );
                    
                    //For avg Speed
                    if($speed >= 1) {
                        $speeds[] = $speed;
                    }
                    
                    //Max Speed
                    $maxSpeed = max($maxSpeed, $speed);
                }
            }
            
            // Min and max height
            $minheight = min($minheight, $track[$i]['altitude']);
            $maxheight = max($maxheight, $track[$i]['altitude']);
        }
        
        //Avg Speed
        if(count($speeds) > 2)
        {
            $avgSpeed = floatval(median($speeds));
        }
        else
        {
            $avgSpeed = 0;
        }
        
        $time = intval($track[$amount - 1]['datetime']->format('U')) - intval($track[0]['datetime']->format('U'));
        
        $distanceIniEnd = distance(
                        $track[0]['latitude'], $track[0]['longitude'], 0,
                        $track[($amount-1)]['latitude'], $track[($amount-1)]['longitude'], 0);
        $isLoop = $distanceIniEnd > 700 ? false : true;
		
		return array(
            'distance' => round( $distance / 1000, 2),
			'uphill' => round($upHill),
			'downhill' => round($downHill),
			'minheight' => round($minheight),
			'maxheight' => round($maxheight),
			'avgspeed' => round($avgSpeed, 1),
			'maxspeed' => round($maxSpeed, 1),
			'time' => $time,
			'isloop' => $isLoop
        );
    }
    
    public function guessName($lat, $lng)
    {
        $content = getContent('http://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&sensor=false');
        
        $json = json_decode($content, 1);
        
        if(count($json['results']))
        {
            $firstresult = $json['results'][0]['address_components'];
            
            foreach($firstresult as $r)
            {
                if(in_array('political',$r['types']))
                {
                    return $r['long_name'];
                }
            }
            
            return $firstresult[0]['long_name'];
        }
        else
        {
            return '';
        }
    }
    
    public function safeToken($token)
    {
        return preg_replace("#[^a-zA-Z0-9.\-]#",'',$token);
    }
}

function median()
{
    $args = func_get_args();

    switch(func_num_args())
    {
        case 0:
            trigger_error('median() requires at least one parameter',E_USER_WARNING);
            return false;
            break;

        case 1:
            $args = array_pop($args);
            // fallthrough

        default:
            if(!is_array($args)) {
                trigger_error('median() requires a list of numbers to operate on or an array of numbers',E_USER_NOTICE);
                return false;
            }

            sort($args);
           
            $n = count( $args );
            $h = intval( $n / 2 );

            if($n % 2 == 0) {
                $median = ( $args[$h] + $args[$h-1] ) / 2;
            } else {
                $median = $args[$h];
            }

            break;
    }
   
    return $median;
}

// distance in metres between two points
function distance($lat1, $lng1, $alt1, $lat2, $lng2, $alt2) 
{ 
    $lat1 = floatval($lat1);
    $lat2 = floatval($lat2);
    $lng1 = floatval($lng1);
    $lng2 = floatval($lng2);
    $alt1 = floatval($alt1);
    $alt2 = floatval($alt2);
    
	$distKM = 6371000 * 2 * asin( sqrt( pow( sin( deg2rad( $lat1  - $lat2 ) / 2 ), 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * pow( sin( deg2rad($lng1-$lng2) / 2 ), 2) )); //harvesinus formula
	
	if($lat1 > 60 OR $lat2 > 60)
	{
	   $distKM *= 0.9966;
    }
    
	$dist = sqrt( pow( $distKM, 2 ) + pow( ( $alt1 - $alt2 ), 2 ) );
	
	return floatval($dist);
}

function safeDateTime($date, $time) 
{
	if(!$date) return new \DateTime('today');
	
	return new \DateTime($date.' '.$time);
}

function getContent($url) 
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 7);
	$content = curl_exec($ch); //return result 
	
	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 403) 
	{
		return null;
	}
	
	if (curl_errno($ch)) 
	{
		return false; //this stops the execution under a Curl failure
	}
	
	curl_close($ch); //close connection_aborted()
	
	return $content;
}