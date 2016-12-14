<?php
header("Content-Type: application/json");

session_start();

if (!isset($_SESSION["validated"])){
	echo "Contact admin@evhighwaystatus.co.uk for access.";
	exit();
}



$apikey = "AIzaSyB9_vfqgqGrIwOeY3tN9tHztRVEU_J7JwM";

$routeRequest = json_decode(stripslashes(file_get_contents("php://input")),true);



$startLocation = $routeRequest["start"]["lat"].",".$routeRequest["start"]["lng"];
$endLocation = $routeRequest["end"]["lat"].",".$routeRequest["end"]["lng"];

foreach ($routeRequest["waypoints"] as $key => $value) {
	$waypoints[count($waypoints)]["location"] = $value["lat"].",".$value["lng"];
	$waypoints[count($waypoints)]["start"] = $value["startStep"]["lat"].",".$value["startStep"]["lng"];
	$waypoints[count($waypoints)]["end"] = $value["endStep"]["lat"].",".$value["endStep"]["lng"];
}

$baseurl = "https://maps.googleapis.com/maps/api/directions/json?";

//how many requests do we need?

$requests = count($waypoints) / 5;
$requests = ceil($requests);

for ($i=0; $i < $requests; $i++) { 
	$waypointsAdded = 0;
	while ($waypointsAdded < 5 && count($waypoints)){
		$waypointsSplit[$i][count($waypointsSplit[$i])] = $waypoints[0];
		$waypointsAdded++;
		//remove the added waypoint
		array_splice($waypoints, 0, 1);
	}
}

var_dump($waypointsSplit);





// //For each request required, construct URL and make call.
// for ($i=0; $i < $requests ; $i++) { 

// 	$url = "origin=".$waypoints[0]["startStep"]."&waypoints=";
// 	$url .= $endLocation."|";

// 	//add waypoints
// 	$waypointsAdded = 0;

// 	while ($waypointsAdded < 5 && count($waypoints)) {
		
// 		$url .= $startLocation."|";
// 		$url .= $waypoints[0];
// 		$waypointsAdded++;
// 		if ($waypointsAdded < 7) {
// 			$url .= "|";
// 			$url .= $endLocation;
// 			$url .= "|";
// 		}
// 		//remove the added waypoint
// 		array_splice($waypoints, 0, 1);
// 	}

// 	$url = substr($url, 0, strlen($url) - 1);

// 	$url .= "&destination=".$endLocation;
// 	$url .= "&key=".$apikey;

// 	$url = $baseurl.$url;

// 	$response = json_decode(goFetch($url),true);

// 	var_dump($url);
// 	var_dump($response);

// }













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



?>