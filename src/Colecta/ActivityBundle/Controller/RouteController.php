<?php

namespace Colecta\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Colecta\ActivityBundle\Entity\Route;


class RouteController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $items = $em->getRepository('ColectaActivityBundle:Route')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        $categories = $em->getRepository('ColectaItemBundle:Category')->findAll();
        
        return $this->render('ColectaActivityBundle:Route:index.html.twig', array('items' => $items, 'categories' => $categories));
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
        $file = new UploadedFile($_FILES['file']['tmp_name'],$_FILES['file']['name']);
        
        if(!$user) 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif (null === $file) 
        {
            $this->get('session')->setFlash('error', 'Ha ocurrido un error al subir el archivo.');
        }
        else
        {
            $extension = $this->extension($file->getClientOriginalName());
            
            if(!$extension) //Extension not accepted
            {
                $this->get('session')->setFlash('error', 'El archivo no tiene una extensión correcta.');
            }
            else
            {
                $hashName = sha1($file->getClientOriginalName() . $user->getId() . mt_rand(0, 99999));
                $filename = $hashName . '.' . $extension;
                
                $uploaddir = 'uploads/routes';
                $rootdir = __DIR__ . '/../../../../web/' . $uploaddir;
                
                $file->move($rootdir, $filename);
                unset($file);
                
                $track = $this->extractTrack($rootdir.'/'.$filename, 500);
                
                return $this->render('ColectaActivityBundle:Route:filldata.html.twig', array('filename' => $filename, 'uploaddir' => $uploaddir, 'track' => $track));
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
        $request = $this->get('request')->request;
        
        $category = $em->getRepository('ColectaItemBundle:Category')->findOneById($request->get('category'));
    
        if(!$user) 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif(!$request->get('description'))
        {
            $this->get('session')->setFlash('error', 'No has escrito ningun texto');
        }
        elseif(!$category)
        {
            $this->get('session')->setFlash('error', 'No existe la categoria');
        }
        else
        {
            $route = new Route();
            $route->setCategory($category);
            $route->setAuthor($user);
            $route->setName($request->get('name'));
            
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
            
            $route->summarize($request->get('description'));
            $route->setAllowComments(true);
            $route->setDraft(false);
            $route->setActivity(null);
            $route->setDateini(new \DateTime(trim($request->get('dateini')).' '.$request->get('dateinihour').':'.$request->get('dateiniminute')));
            $route->setDateend(new \DateTime(trim($request->get('dateend')).' '.$request->get('dateendhour').':'.$request->get('dateendminute')));
            $route->setShowhours(false);
            $route->setDescription($request->get('description'));
            $route->setDistance($request->get('distance'));
            $route->setUphill($request->get('uphill'));
            $route->setDownhill($request->get('downhill'));
            $route->setDifficulty($request->get('difficulty'));
            $route->setStatus('');
            
            $em->persist($route); 
            $em->flush();
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaRouteIndex');
        }
        
        return new RedirectResponse($referer);
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
				
				if($latitude !== null) { $trackpoint['latitude'] = $l[$latitude];}
				if($longitude !== null) { $trackpoint['longitude'] = $l[$longitude];}
				if($altitude !== null) { $trackpoint['altitude'] = $l[$altitude];}
				if($name !== null) { $trackpoint['name'] = $l[$name];}
				if($date !== null) { $trackpoint['date'] = $l[$date];}
				if($time !== null) { $trackpoint['time'] = $l[$time];}
				
				$track[] = $trackpoint;
			}
			
			return $track;
		}
		
		return null;
    }
}
