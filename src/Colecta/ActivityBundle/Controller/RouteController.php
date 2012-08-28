<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Colecta\ActivityBundle\Form\Frontend\RouteType;
use Colecta\ActivityBundle\Entity\Route;
use Colecta\ActivityBundle\Entity\RouteTrackpoint;


class RouteController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $items = $em->getRepository('ColectaActivityBundle:Route')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaActivityBundle:Route:index.html.twig', array('items' => $items));
    }
    public function viewAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $item = $em->getRepository('ColectaActivityBundle:Route')->findOneBySlug($slug);
        
        return $this->render('ColectaActivityBundle:Route:full.html.twig', array('item' => $item));
    }
    public function uploadAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if(!$user) 
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
                
                $rootdir = $this->getUploadDir();
                
                $file->move($rootdir, $filename);
                unset($file);
                
                $track = $this->extractTrack($rootdir.'/'.$filename, 500); //simplified to 500 points only
                                
                if(!$track)
                {
                    $this->get('session')->setFlash('error', 'No se ha podido leer correctamente el archivo.');
                }
                else
                {
                    $route = new Route();
                    $form = $this->createForm(new RouteType(), $route);
                    
                    $fulltrack = $this->extractTrack($rootdir.'/'.$filename); //full track
                    $routedata = $this->getRouteData($fulltrack);
                    
                    $categories = $this->getDoctrine()->getEntityManager()->getRepository('ColectaItemBundle:Category')->findAll();
                    
                    return $this->render('ColectaActivityBundle:Route:filldata.html.twig', array('filename' => $filename, 'track' => $track, 'trackdata' => $routedata, 'form' => $form->createView(), 'categories' => $categories));
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
            elseif(!$category)
            {
                $this->get('session')->setFlash('error', 'No existe la categoria');
            }
            else
            {
                $route = new Route();
                $form = $this->createForm(new RouteType(), $route);
                $form->bindRequest($request);
                
                if ($form->isValid()) 
                {
                    $route->setCategory($category);
                    $route->setAuthor($user);
                    
                    //Slug generate
                    $slug = $route->generateSlug();
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
                    $route->setSlug($slug);
                    
                    $route->summarize($route->getDescription());
                    $route->setAllowComments(true);
                    $route->setDraft(false);
                    $route->setActivity(null);
                    $route->setDifficulty($post->get('difficulty'));
                    
                    $time = intval($post->get('days')) * 24 * 60 * 60 + intval($post->get('hours')) * 60 * 60 + intval($post->get('minutes')) * 60;
                    $route->setTime($time);
                    
                    $route->setIsloop(false);
                    $route->setIBP('');
                    $route->setIsloop(intval($post->get('isloop')));
                    $route->setSourcefile($post->get('filename'));
                    
                    $em->persist($route); 
                    
                    //Now retrieve and save RouteTrackpooints
                    $filename = $post->get('filename');
                    $rootdir = $this->getUploadDir();
                    
                    $fulltrack = $this->extractTrack($rootdir.'/'.$filename, 10000); //the track, limited to 10000 points for performance reasons
                    
                    foreach($fulltrack as $point)
                    {
                        $trackpoint = new RouteTrackpoint();
                        $trackpoint->setLatitude($point['latitude']);
                        $trackpoint->setLongitude($point['longitude']);
                        $trackpoint->setAltitude($point['altitude']);
                        $trackpoint->setDate($point['datetime']);
                        $trackpoint->setRoute($route);
                        
                        
                        $em->persist($trackpoint); 
                        unset($trackpoint);
                    }
                    
                    $em->flush();
                    
                    return new RedirectResponse($this->generateUrl('ColectaRouteView', array('slug' => $slug)));
                }
                else
                {
                    $this->get('session')->setFlash('error', 'Revisa los campos');
                    
                    $filename = $post->get('filename');
                    $rootdir = $this->getUploadDir();
                    
                    $track = $this->extractTrack($rootdir.'/'.$filename, 500); //simplified to 500 points only
                    $fulltrack = $this->extractTrack($rootdir.'/'.$filename); //full track
                    $routedata = $this->getRouteData($fulltrack);
                    
                    $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
                    
                    return $this->render('ColectaActivityBundle:Route:filldata.html.twig', array('filename' => $filename, 'track' => $track, 'trackdata' => $routedata, 'form' => $form->createView(), 'categories' => $categories));
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