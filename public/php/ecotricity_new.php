<?php 
header("Content-Type: text/plain");

ignore_user_abort(true);
set_time_limit(0);


// $response = goFetch("https://www.ecotricity.co.uk/api/ezx/v1/getPumpList", array(
// 	"appId" => "com.ecotricity.electrichighway",
// 	"latitude" => 52.164,
// 	"longitude" => 0.4762,
// 	"radius" => 1000,

// ));

$locations = [];
$isLocation = true;
$i = 0;

while ($i < 300) { 

		$i++;

		$response = goFetch("https://www.ecotricity.co.uk/api/ezx/v1/getLocationDetails", array(
			"vehicleMake" => "Nissan",
			"vehicleModel" => "Leaf",
			"appId" => "com.ecotricity.electrichighway",
			"locationId" => $i,
			"vehicleSpecification" => "(2011-)",
		));

		$response = json_decode($response,true);

		if (!$response["result"]) {
		
		} else {
			array_push($locations, $response["result"]["pump"]);
		}
		
		sleep(0.5);
}	


$eLocations = [];

//var_dump($locations);

if (count($locations) > 125) {
	
	foreach ($locations as $key => $pumps) {

		foreach ($pumps as $key => $pump) {
			$thisLocation = [];
			$thisLocation["provider"] = "Ecotricity";
			$thisLocation["isBeta"] = true;
			$thisLocation["provider_openid"] = 24;	
			$thisLocation["name"] = $pump["name"]." - ".$pump["location"]; 
			$thisLocation["postcode"] = $pump["postcode"];
			$thisLocation["lat"] = floatval($pump["latitude"]);
			$thisLocation["lng"] = floatval($pump["longitude"]);
			$thisLocation["lastHeartbeat"] = str_replace("Z", "", $pump["lastHeartbeat"]) ;
			$thisLocation["chargerId"] = floatval($pump["pumpId"]);
			$thisLocation["source"]["name"] = "Ecotricity";
			$thisLocation["source"]["url"] = "http://www.ecotricity.co.uk/for-the-road/our-electric-highway";
			$thisLocation["connectors"] = [];
			
			foreach ($pump["connector"] as $key => $connector) {
				$thisConnector = [];
				$thisConnector["quantity"] = 1;

				switch ($connector["type"]) {

					case "DC (CHAdeMO)":
						$thisConnector["type"]["title"] = "CHAdeMO";
						$thisConnector["type"]["id"] = 1;
						$thisConnector["power"] = 50;
						break;

					case "AC (RAPID)":
						$thisConnector["type"]["title"] = "AC (tethered)";
						$thisConnector["type"]["id"] = 3;
						$thisConnector["power"] = 43;
						break;

					case "AC (Medium)":
						$thisConnector["type"]["title"] = "AC (socket)";
						$thisConnector["type"]["id"] = 4;
						$thisConnector["power"] = 22;
						break;	

					case "CCS":
						$thisConnector["type"]["title"] = "CCS";
						$thisConnector["type"]["id"] = 2;
						$thisConnector["power"] = 50;
						break;

					default:
						$thisConnector["type"]["title"] = "unknown";
						$thisConnector["type"]["id"] = 0;
						$thisConnector["power"] = 0;
						break;
				}

				switch ($connector["status"]) {

					case "Available":
						$thisConnector["status"] = "online";
						break;

					case "Swipe card only":
						$thisConnector["status"] = "online";
						break;

					case "Offline":
						$thisConnector["status"] = "offline";
						break;

					case "In use":
						$thisConnector["status"] = "occupied";
						break;	

					default:
						$thisConnector["status"] = "unknown";
						break;
				}

				array_push($thisLocation["connectors"], $thisConnector);
			}

			array_push($eLocations,$thisLocation);
		}

	}

	$jsonObject = [];
	$jsonObject["locations"] = $eLocations;
	$jsonObject["last_updated"] = date('j M H:i');

	var_dump($locations);

	$myfile = fopen("../json/ecotricity_new.json","w");
	fwrite($myfile, json_encode($jsonObject));
	fclose($myfile);
}




// Custom function to use cookies.
function goFetch($url, $params = null)
{
	// Our cookies.
	global $mmmCookies;
	// The cURL handle.
	$ch = curl_init($url);
	// If we got some POST params to use.
	if(is_array($params))
	{
		// Sort the params.
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
	}
	// Set a modern UA string.
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: EVHighwayStatus.co.uk - Hope you don't mind Simon, only once every 30 minutes, until API ready? :) Andrew "));
	// Follow redirects if we get them.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	// Return the output as a string instead of outputting it.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// How long to wait before we give up on Nissan.
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	// Where to save cookies when we're done.
	curl_setopt($ch, CURLOPT_COOKIEJAR, $mmmCookies);
	// Which cookies to use.
	curl_setopt($ch, CURLOPT_COOKIEFILE, $mmmCookies);
	// Execute the request.
	$response = curl_exec($ch);
	// If cURL returned an error.
	if(curl_error($ch) != ""){
        // TODO - The request failed. Do something.
		return false;
	}
	
	// Return the response.
	return $response;
}



?>
