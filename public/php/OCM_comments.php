<?php
header("Content-Type: application/json");
require("database_r2.php");

session_start();

if (!isset($_SESSION["validated"])){
	echo "Contact admin@evhighwaystatus.co.uk for access.";
	exit();
}

$chargers = json_decode(stripslashes(file_get_contents("php://input")),true);

for ($i=0; $i < count($chargers) ; $i++) { 

	//$url = "http://api.openchargemap.io/v2/poi/?output=json&countrycode=GB&maxresults=1&latitude=".$chargers[$i]["lat"]."&longitude=".$chargers[$i]["lng"]."&distance=0.1&operatorid=".$chargers[$i]["operatorid"];
	if(isset($chargers[$i]["operator_id"])){
		$data = getOCMChargers(["operator_id" => $chargers[$i]["operator_id"]]);
	} else {
		$data = getOCMChargers();
	}

	$distance = 9999;
	$closest = -1;

	if(is_array($data) && count($data) > 0){
		foreach ($data as $key => $value) {

			$thisDistance = calcDistance($value["lat"],$value["lng"],$chargers[$i]["lat"],$chargers[$i]["lng"]);

			if ($thisDistance < $distance){
				$distance = $thisDistance;
				$closest = $key;
			}
		}

		if ($closest > -1 && $distance < 0.1){
			$commentCharger = json_decode($data[$closest]["json_data"],true);
			$chargers[$i]["comments"] = $commentCharger["UserComments"];
		} else {
			$chargers[$i]["comments"] = null;
		}	

	} else {
		$chargers[$i]["comments"] = null;
	}

	
}
//var_dump($data);
echo json_encode($chargers);

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
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: EVHighwayStatus.co.uk"));
	// Follow redirects if we get them.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	// Return the output as a string instead of outputting it.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// How long to wait before we give up on Nissan.
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	// Where to save cookies when we're done.
	curl_setopt($ch, CURLOPT_COOKIEJAR, $mmmCookies);
	// Which cookies to use.
	curl_setopt($ch, CURLOPT_COOKIEFILE, $mmmCookies);
	// Execute the request.
	$response = curl_exec($ch);
	// If cURL returned an error.
	if(curl_error($ch) != ""){
        // TODO - The request failed. Do something.
		exit;
	}
	
	// Return the response.
	return $response;
}

function calcDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) {
	$earthMeanRadius = 6371;

	$deltaLatitude = deg2rad($latitudeTo - $latitudeFrom);
	$deltaLongitude = deg2rad($longitudeTo - $longitudeFrom);

    $a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2) +
          cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) *
          sin($deltaLongitude / 2) * sin($deltaLongitude / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthMeanRadius * $c;
   
}


?>