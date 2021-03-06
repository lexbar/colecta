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
        
        $em = $this->getDoctrine()->getManager();
        
        $findby = array('draft'=>0);
        
        if(!$this->getUser() || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $findby['open'] = 1;
        }
        
        //Get ALL the items that are accessible
        $items = $em->getRepository('ColectaActivityBundle:Route')->findBy($findby, array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE c.routes > 0 ORDER BY c.name ASC'
        )->setFirstResult(0);
        
        $categories = $query->getResult();
        
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
    public function categoryAction($slug)
    {
        return $this->categoryPageAction($slug, 1);
    }    
    public function categoryPageAction($slug, $page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getManager();
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneBySlug($slug);
        
        $SQLprivacy = '';
        
        if(!$this->getUser() || $this->getUser()->getRole()->is('ROLE_BANNED'))
        {
            $SQLprivacy = ' AND i.open = 1 ';
        }
        
        $items = $em->createQuery(
            "SELECT i FROM ColectaItemBundle:Item i WHERE i.draft = 0 $SQLprivacy AND i.category = :category AND i INSTANCE OF Colecta\ActivityBundle\Entity\Route ORDER BY i.date DESC"
        )->setParameter('category',$category->getId())->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1)->getResult();
        
        $query = $em->createQuery(
            'SELECT c FROM ColectaItemBundle:Category c WHERE c.routes > 0 ORDER BY c.name ASC'
        )->setFirstResult(0);
        
        $categories = $query->getResult();
        
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
        
        return $this->render('ColectaActivityBundle:Route:category.html.twig', array('category'=>$category, 'items' => $items, 'categories' => $categories, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Route')->findOneBySlug($slug);
        
        $user = $this->getUser();
        
        if(!$item)
        {
            $this->get('session')->getFlashBag()->add('error', 'No hemos encontrado la ruta que estás buscando');
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        if(($item->getDraft() && (! $user || $user->getId() != $item->getAuthor()->getId() )) || ((!$user || $user->getRole()->is('ROLE_BANNED')) && !$item->getOpen()))
        {
            $this->get('session')->getFlashBag()->add('error', 'No tienes permisos para ver esta ruta');
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        return $this->render('ColectaActivityBundle:Route:full.html.twig', array('item' => $item));
    }
    public function mapAction($id)
    {
        $this->get('request')->setRequestFormat('image');
        
        $cacheDir = __DIR__ . '/../../../../app/cache/prod/images/maps';
        $cachePath = $cacheDir . '/' . $id .'.png';
        
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
            $response->headers->set('Content-Type','image/png');
            
            return $response;
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            
            
            $item = $em->getRepository('ColectaActivityBundle:Route')->findOneById($id);
            
            $user = $this->getUser();
            
            if(!$item)
            {
                return new Response('Page not found.', 404);
            }
            if(($item->getDraft() && (! $user || $user->getId() != $item->getAuthor()->getId() )) || (!$user && !$item->getOpen()))
            {
                return new Response('Forbidden.', 403);
            }
            
            $itemtrack = $item->getTrackpoints();
            
            $n = count($itemtrack);
            
            if($n > 0) 
            {
                $url = "http://maps.google.com/maps/api/staticmap?size=640x200&maptype=terrain&sensor=false&path=color:0xf61e00|weight:3";
                $step = floor($n / 60);
                for($i = 0; $i < $n; $i += $step)
                {
                    $url .= '|'. $itemtrack[$i]->getLatitude() .','. $itemtrack[$i]->getLongitude();
                }
                
                $url .= "&key=" . $this->container->getParameter('maps_api_key');
                
                $image = getContent($url);
                
                //Write file to cache
                if(!is_dir($cacheDir))
                {
                    // dir doesn't exist, make it
                    mkdir($cacheDir, 0755, true);
                }
                file_put_contents($cachePath, $image);
            }
            else 
            {
                return new Response('Page not found.', 404);
            }
        }
        
        $response->setContent($image);
        $response->headers->set('Content-Type', 'image/png');
        return $response;
    }
    public function profileAction($id)
    {
        $this->get('request')->setRequestFormat('image');
        
        $cacheDir = __DIR__ . '/../../../../app/cache/prod/images/maps';
        $cachePath = $cacheDir . '/' . $id .'-profile.svg' ;
        
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
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            
            
            $item = $em->getRepository('ColectaActivityBundle:Route')->findOneById($id);
            
            $user = $this->getUser();
            
            if(!$item)
            {
                return new Response('Page not found.', 404);
            }
            if(($item->getDraft() && (! $user || $user->getId() != $item->getAuthor()->getId() )) || (!$user && !$item->getOpen()))
            {
                return new Response('Forbidden.', 403);
            }
            
            $track = $item->getTrackpoints();
            
            $coordinates = array();
            
            $n = count($track);
            if($n > 100) 
            { 
                $step = floor($n / 60);
            }
            else
            {
                $step = 1;
            }
            
            $distance = $avgheight = $maxheight = 0;
            
            for($i = 0; $i < $n-1; $i++)
            {
                $j = $i +1;
                // Distance
                $distance += distance(
                    $track[$i]->getLatitude(), $track[$i]->getLongitude(), $track[$i]->getAltitude(),
                    $track[$j]->getLatitude(), $track[$j]->getLongitude(), $track[$j]->getAltitude()
                );
                
                $avgheight += $track[$i]->getAltitude();
                
                if($i != 0 && $i % $step == 0)
                {
                    $avgheight = $avgheight / $step;
                    $coordinates[] = array($distance, $avgheight);
                    
                    $maxheight = max($avgheight, $maxheight);
                    $maxwidth = $distance;
                }
            }
            $image = $this->renderView('ColectaActivityBundle:Route:profile.svg.twig', array('coordinates' => $coordinates, 'maxheight' => $maxheight, 'maxwidth' => $maxwidth));
            
            //Write file to cache
            if(!is_dir($cacheDir))
            {
                // dir doesn't exist, make it
                mkdir($cacheDir, 0755, true);
            }
            file_put_contents($cachePath, $image);
        }
        
        $response->setContent($image);
        $response->headers->set('Content-Type', 'image/svg+xml');
        return $response;
    }
    public function newAction()
    {
        $user = $this->getUser();
        
        if(!$user || !$user->getRole()->getItemRouteCreate()) 
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        return $this->render('ColectaItemBundle:Default:newItem.html.twig', array('type' => 'Route'));
    }
    public function uploadAction()
    {
        $user = $this->getUser();
        
        if(!$user || !$user->getRole()->getItemRouteCreate()) 
        {
            $this->get('session')->getFlashBag()->add('error', 'Error, debes iniciar sesion');
        }
        else
        {
            if (!$_FILES['file'] || ($file = new UploadedFile($_FILES['file']['tmp_name'],$_FILES['file']['name'])) === null) 
            {
                $this->get('session')->getFlashBag()->add('error', 'Ha ocurrido un error al subir el archivo.');
            }
            
            $extension = $this->extension($file->getClientOriginalName());
            
            if(!$extension) //Extension not accepted
            {
                $this->get('session')->getFlashBag()->add('error', 'El archivo no tiene una extensión correcta.');
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
                    $this->get('session')->getFlashBag()->add('error', 'No se ha podido leer correctamente el archivo.');
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
                    
                    $categories = $this->getDoctrine()->getManager()->getRepository('ColectaItemBundle:Category')->findAll();
                    
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
        
        $user = $this->getUser();
        
        if(!$user || !$user->getRole()->getItemRouteCreate()) 
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
        $user = $this->getUser();
        
        if(!$user || !$user->getRole()->getItemRouteCreate()) 
        {
            throw $this->createNotFoundException();
        }
        else
        {
            $response = new Response();
            $response->setStatusCode(200);
        
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
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $request = $this->get('request');
        $post = $request->request;
        
        if(!$user || !$user->getRole()->getItemRouteCreate()) 
        {
            $this->get('session')->getFlashBag()->add('error', 'No tienes permisos para publicar rutas');
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        if($this->get('request')->getMethod() == 'POST')
        {
            $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
        
            if(!$category && !$request->get('newCategory'))
            {
                $this->get('session')->getFlashBag()->add('error', 'No existe la categoria');
            }
            else
            {
                $item = new Route();
                $form = $this->createForm(new RouteType(), $item);
                $form->bind($request);
                
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
                                $subtract = $num_length = strlen((string)$n) + 1 ;
                                $catSlug = substr($slug,0,-$subtract);
                            }
                            
                            $catSlug .= '-'.$n;
                            
                            $n++;
                        }
                    
                        $category->setSlug($catSlug);
                        $category->setText('');
                    }
                    
                    $category->setLastchange(new \DateTime('now'));
                    $em->persist($category); 
                    
                    $item->setCategory($category);
                    $item->setAuthor($user);
                    
                    //if comes from itemSubmit i have to fill name and text
                    if($post->get('name'))
                    {
                        $item->setName($post->get('name'));
                    }
                    
                    if($post->get('text'))
                    {
                        $item->setText($post->get('text'));
                    }
                    
                    //Move file out of cache to accesible folder
                    $cachePath = __DIR__ . '/../../../../app/cache/prod/files' ;
                    
                    $filename = $this->safeToken($post->get('filename'));
                    
                    if(!file_exists($cachePath.'/'.$filename))
                    {
                        $this->get('session')->getFlashBag()->add('error', 'No hemos podido procesar el archivo de ruta');
                        return $this->render('ColectaItemBundle:Default:newItem.html.twig', array('type' => 'Route', 'item'=>$item));
                    }
                    
                    //Upload file
                    $filesystem = $this->container->get('knp_gaufrette.filesystem_map')->get('uploads');
                    $filesystem->write('routes/' . $filename , file_get_contents($cachePath.'/'.$filename) );
                    
                    $fulltrack = $this->extractTrack($cachePath.'/'.$filename, 10000); //the track, limited to 10000 points for performance reasons
                    
                    if( ! $item->getName() )
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
                            $subtract = $num_length = strlen((string)$n) + 1 ;
                            $slug = substr($slug,0,-$subtract);
                        }
                        
                        $slug .= '-'.$n;
                        
                        $n++;
                    }
                    $item->setSlug($slug);
                    
                    $item->summarize(strval($item->getText()));
                    
                    $item->setAllowComments(true);
                    $item->setDraft(false);
                    $item->setOpen($request->get('open'));
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
                    
                    if(!$item->getText())
                    {
                        $item->setText('');
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
                    $points = $this->extractPoints($cachePath.'/'.$filename); //the waypoints
                    
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
                        
                        if(!empty($point['text']))
                        {
	                        $place->setText($point['text']);
                        }
                        else
                        {
	                        $place->setText('');
                        }
                        
                        $place->summarize($place->getText());
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
                                $subtract = $num_length = strlen((string)$n) + 1 ;
                                $slug = substr($slug,0,-$subtract);
                            }
                            
                            $slug .= '-'.$n;
                            
                            $n++;
                        }
                        $place->setSlug($slug);
                        
                        $relation = new Relation();
                        $relation->setUser($user);
                        $relation->setItemfrom($item);
                        $relation->setItemTo($place);
                        if(!$place->getName())
                        {
	                        $relation->setText('');
                        }
                        else
                        {
	                        $relation->setText($place->getName());
	                    }
                        $em->persist($relation); 
                        
                        $place->addRelatedto($relation);
                        
                        $em->persist($place); 
                    }
                    
                    $em->flush();
                    
                    /*$this->get('session')->getFlashBag()->add('success', 'Ruta publicada correctamente.');*/
                    
                    return new RedirectResponse($this->generateUrl('ColectaRouteView', array('slug' => $item->getSlug())));
                }
                else
                {
                    $this->get('session')->getFlashBag()->add('error', 'No se ha podido publicar, por favor revisa los campos.');
                    
                    $item = new Route();
		            $item->setText($request->get('text'));
		            $item->setName($request->get('name'));
		            
		            if($post->get('filename'))
		            {
    		            $routeToken = $this->safeToken($post->get('filename'));
		            }
		            else 
		            {
    		            $routeToken = null;
		            }
		            
		            return $this->render('ColectaItemBundle:Default:newItem.html.twig', array('type' => 'Route', 'item'=>$item, 'routeToken'=>$routeToken));
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
        $em = $this->getDoctrine()->getManager();
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
                $this->get('session')->getFlashBag()->add('error', 'No existe la categoria');
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
                            $subtract = $num_length = strlen((string)$n) + 1 ;
                            $catSlug = substr($slug,0,-$subtract);
                        }
                        
                        $catSlug .= '-'.$n;
                        
                        $n++;
                    }
                
                    $category->setSlug($catSlug);
                    $category->setText('');
                    $category->setLastchange(new \DateTime('now'));
                    
                    $em->persist($category); 
                }
                
                $item->setCategory($category);
            }
            
            $form->bind($request);
            
            //File replacement
            if ($persist && isset($_FILES['file']) && $_FILES['file']['tmp_name'])
            {
                if(($file = new UploadedFile($_FILES['file']['tmp_name'],$_FILES['file']['name'])) === null)
                {
                    $this->get('session')->getFlashBag()->add('error', 'No se ha podido subir el archivo.');
                    $persist = false;
                }
                else
                {
                    $extension = $this->extension($file->getClientOriginalName());
            
                    if(!$extension) //Extension not accepted
                    {
                        $this->get('session')->getFlashBag()->add('error', 'El archivo no tiene una extensión correcta.');
                        $persist = false;
                    }
                    else
                    {
                        $hashName = sha1($file->getClientOriginalName() . $user->getId() . mt_rand(0, 99999));
                        $filename = $hashName . '.' . $extension;
                        
                        //Upload file
	                    $filesystem = $this->container->get('knp_gaufrette.filesystem_map')->get('uploads');
	                    $filesystem->write('routes/' . $filename , file_get_contents($_FILES['file']['tmp_name']));
                        
                        //FIX. Rename cache file so it has appropiate extension
                        $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
                        rename($_FILES['file']['tmp_name'], $_FILES['file']['tmp_name'] . '.' . $extension);
                        
                        $track = $this->extractTrack($_FILES['file']['tmp_name'] . '.' . $extension, 10000); //the track, limited to 10000 points for performance reasons
                                                
                        //unset($file);
                                        
                        if(!$track)
                        {
                            $this->get('session')->getFlashBag()->add('error', 'No se ha podido leer correctamente el archivo.');
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
            
            $item->summarize($item->getText());
            $item->setDifficulty($post->get('difficulty'));
            $time = intval($post->get('days')) * 24 * 60 * 60 + intval($post->get('hours')) * 60 * 60 + intval($post->get('minutes')) * 60;
            $item->setOpen($request->get('open'));
            
            if($persist)
            {
                $em->persist($item); 
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Modificado con éxito.');
            }
        }
        
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        return $this->render('ColectaActivityBundle:Route:edit.html.twig', array('item' => $item, 'categories'=>$categories, 'form' => $form->createView()));
    }
    
    public function deleteAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
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
        
        if(file_exists($this->getUploadDir() . '/' . $item->getSourcefile()))
        {
            unlink($this->getUploadDir() . '/' . $item->getSourcefile());
        }
        
        $em->remove($item);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('success', '"'.$name.'" ha sido eliminado.');
        
        return new RedirectResponse($this->generateUrl('ColectaDashboard'));
    }
    
    public function downloadAction($slug, $extension)
    {
        $this->get('request')->setRequestFormat($extension);
        
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('ColectaActivityBundle:Route')->findOneBySlug($slug);
        $user = $this->getUser();
        
        if(!$item)
        {
            $this->get('session')->getFlashBag()->add('error', 'No hemos encontrado la ruta que estás buscando');
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        if(($item->getDraft() && (! $user || $user->getId() != $item->getAuthor()->getId() )) || ((!$user || $user->getRole()->is('ROLE_BANNED')) && !$item->getOpen()))
        {
            $this->get('session')->getFlashBag()->add('error', 'No tienes permisos para ver esta ruta');
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $oformat = $this->acceptedExtensions($extension);
        
        if($oformat)
        {
            $cacheDir = __DIR__ . '/../../../../app/cache/prod/files/';
            $cachePath = $cacheDir . $item->getSourcefile() ;
            
            //Write file to cache
            if(!is_dir($cacheDir))
            {
                // dir doesn't exist, make it
                mkdir($cacheDir, 0755, true);
            }
            
            if( ! file_exists($cachePath) )
            {
                //Download to cache gps file
                
                $filesystem = $this->container->get('knp_gaufrette.filesystem_map')->get('uploads');
                file_put_contents( $cachePath, $filesystem->read( 'routes/' . $item->getSourcefile() ) );
            }
            
            $iformat = $this->acceptedExtensions($this->extension($item->getSourcefile()));
            $simplified = $this->get('request')->query->get('simplified');
            
            if($simplified && is_numeric($simplified))
            {
                $out = shell_exec("gpsbabel -t -i $iformat -f $cachePath -x simplify,count=$simplified -o $oformat -F -");
            }
            else
            {
                if($iformat != $oformat)
                {
                    $out = shell_exec("gpsbabel -t -i $iformat -f $cachePath -o $oformat -F -");
                }
                else
                {
                    $out = file_get_contents($cachePath);
                }
                
            }
        
            $response = new Response($out);
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename=' . $item->getSlug() . '.' .$extension );
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            
            return $response;
        }
        else
        {
            throw $this->createNotFoundException('El formato '.$oformat.' no existe.');
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
            $csv = shell_exec("gpsbabel -t -i $format -f $filepath -x simplify,count=$simplified -o unicsv -F - 2>&1");
        }
        else
        {
            $csv = shell_exec("gpsbabel -t -i $format -f $filepath -o unicsv -F - 2>&1");
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
			$latitude = $longitude = $name = $altitude = $date = $time = $text = null;
			foreach($csvheader as $row) {
				switch(trim($row)) {
					case 'Latitude': $latitude = $i ; break;
					case 'Longitude': $longitude = $i ; break;
					case 'Name': $name = $i ; break;
					case 'Text': $text = $i ; break;
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
				$point['name'] = $name === null ? '' : preg_replace('#^\"(.*)\"$#', '$1', $l[$name]);
				$point['text'] = $text === null ? '' : preg_replace('#^\"(.*)\"$#', '$1', $l[$text]);
				if($altitude !== null) { $point['altitude'] = $l[$altitude];}
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