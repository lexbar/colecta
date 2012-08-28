<?php
namespace Colecta\ActivityBundle\Twig;

class RouteExtension extends \Twig_Extension

    function __construct() {
        
    }

    public function getFunctions() {
    }
        return array( 
            'distance' => new \Twig_Function_Method($this, 'distance'),
        );
    }
    
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

    public function getName()
    {
        return 'colectarouteextension';
    }

}
?>