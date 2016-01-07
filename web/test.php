<?php
    
    $filepath = '/Users/lexbar/Downloads/track-san-vicente-de-piedrahita-cortes-de-arenoso.kml';
    
    var_dump(extractTrack($filepath, false));
    
    function extractTrack($filepath, $simplified = false)
    {
        //Using gpsbabel we transform whatever format the file is to CVS
        $format = acceptedExtensions(extension($filepath));
        if($simplified && is_numeric($simplified))
        {
            $gpx = shell_exec("gpsbabel -t -i $format -f $filepath -x simplify,count=$simplified -o gpx -F - 2>&1");
        }
        else
        {
            $gpx = shell_exec("gpsbabel -t -i $format -f $filepath -o gpx -F - 2>&1");
        }
        
        $xml = new SimpleXMLElement($gpx);
        
        
        echo "\n Hay ". count($xml->trk) ." tracks: \n";
        
        foreach($xml->trk as $trk)
        {
            echo $trk->name."\n";
        }
        
        echo "\n Hay ". count($xml->wpt) ." waypoints: \n";
        
        foreach($xml->wpt as $wpt)
        {
            echo $wpt->name."\n";
        }
        
        
        
        /*$lines = explode("\n",$csv);
        
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
		
		return null;*/
    }
    function safeDateTime($date, $time) 
    {
    	if(!$date) return new \DateTime('today');
    	
    	return new \DateTime($date.' '.$time);
    }
    function acceptedExtensions($extension = false)
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
    
    function extension($filename)
    {
        $accepted = array_keys(acceptedExtensions());
        
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
?>