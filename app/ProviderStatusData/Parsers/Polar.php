<?php namespace App\ProviderStatusData\Parsers;

use App\ProviderStatusData\Interfaces\Parser;

class Polar implements Parser
{
	/**
	 * Take an HTTP response, and parse it to an array of
	 * of location objects with connectors.
	 *
	 * @param $HttpResponse
	 * @return array Locations and Connectors
	 */
	public function parse($HttpResponse)
	{
		$posts = json_decode($HttpResponse,false)->posts;
		
		$locations = [];

		for ($x=0; $x < count($posts) ; $x++) { //for all of the posts
            $c = [];

            $lat = $posts[$x]->Latitude;
            $lng = $posts[$x]->Longitude;
            
            $locationkey = 0;
            foreach ($locations as $lkey => $location) {
               
                 if ($location['lat'].$location['lng'] == $lat.$lng) {
                     $locationkey = $lkey;
                     
                     break;
                 }
                 $locationkey =  count($locations);
            }             

			$locations[$locationkey]["name"] =  $posts[$x]->Address;;
			$locations[$locationkey]["provider"] = "Polar";
			$locations[$locationkey]["provider_openid"] = 32;
			$locations[$locationkey]["lat"] = floatval($posts[$x]->Latitude);
			$locations[$locationkey]["lng"] = floatval($posts[$x]->Longitude);
			$locations[$locationkey]["postcode"] = $posts[$x]->Postcode;
			$locations[$locationkey]["source"]["url"] = "http://polar-network.com/map/";
			$locations[$locationkey]["source"]["name"] = "Polar";
            
            if (!isset($locations[$locationkey]["connectors"]))
            {
                $locations[$locationkey]["connectors"] = [];
            }

			

			for ($i = 0; $i < count($posts[$x]->Sockets) ; $i++) {

				$s = [];

				switch ($posts[$x]->Sockets[$i]->Rating) {
					case 'Rapid-Chademo':
						$s["type"]["title"] = "CHAdeMO";
						$s["type"]["id"] = 1;
						break;
					case 'Rapid-AC':
						$s["type"]["title"] = "AC (tethered)";
						$s["type"]["id"] = 3;
						break;
					case 'Rapid-Combo':
						$s["type"]["title"] = "CCS";
						$s["type"]["id"] = 2;
						break;
					case 'Type2':
						$s["type"]["title"] = "AC (socket)";
						$s["type"]["id"] = 4;
						break;
					case '13A':
						$s["type"]["title"] = "13A 3-Pin";
						$s["type"]["id"] = 5;
						break;		
					default:
						$s["type"]["title"] = $posts[$x]->Sockets[$i]->Rating;
						$s["type"]["id"] = 0;
						break;		
				}

				switch ($posts[$x]->Sockets[$i]->Status) {
					case 0:
						$s["status"] = "online";
						break;
					case 1:
						$s["status"] ="occupied";
						break;
					case 2:
						$s["status"] = "offline";
						break;	
					default:
						$s["status"] = "unknown";
						break;	
				}
				$s["power"] = $posts[$x]->Sockets[$i]->KW;
				
				$locations[$locationkey]["connectors"][] = (object) $s;
			}

			
		}	
        $locations = array_map(function($value){
            return (object) $value;
        },$locations);
		return $locations;
	}
}
