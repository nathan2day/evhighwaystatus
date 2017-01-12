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
		//dd($posts);
		$locations = [];

		for ($x=0; $x < count($posts) ; $x++) { //for all of the posts
			$c = [];
			$c["name"] = $posts[$x]->Address;
			$c["postcode"] = $posts[$x]->Postcode;
			$c["lat"] = $posts[$x]->Latitude;
			$c["lng"] = $posts[$x]->Longitude;
			$c["sockets"] = [];

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
				
				$c["sockets"][$i] = (object) $s;
			}

			$thislocation = [];

			$thislocation["name"] = $c["name"];
			$thislocation["provider"] = "Polar";
			$thislocation["provider_openid"] = 32;
			$thislocation["lat"] = floatval($c["lat"]);
			$thislocation["lng"] = floatval($c["lng"]);
			$thislocation["postcode"] = $c["postcode"];
			$thislocation["source"]["url"] = "http://polar-network.com/map/";
			$thislocation["source"]["name"] = "Polar";
			$thislocation["connectors"] = $c["sockets"];

			array_push($locations, (object) $thislocation);
			
		}	

		return $locations;
	}
}
