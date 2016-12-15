<?php
header("Content-Type: application/json");

session_start();

if(isset($_SERVER["HTTP_XAPPAUTH"]) &&
   $_SERVER["HTTP_XAPPAUTH"] === "8;iLY3AZ1m7,?[pUKM0!+E7h44;u2W81dWl<(mf85kevN0J-MN^V6P1F47VTE77") {
 	$_SESSION["validated"] = true;
}

if (!isset($_SESSION["validated"])){
	echo "Contact admin@evhighwaystatus.co.uk for access.";
	exit();
}

$v = json_decode(stripslashes(file_get_contents("php://input")),true);
$providers = $v['providers'];

$jsonData = [];
$jsonData["locations"] = [];
$jsonOb = [];


if (is_array($providers)) {

	// for ($i=0; $i < count($providers); $i++) { 
	// 	$jsonFile = file_get_contents("https://evhighwaystatus.co.uk/json/".$providers[$i].".json");
	// 	$jsonOb = json_decode($jsonFile,true);
	// 	$jsonData["last_updated"] = $jsonOb["last_updated"];

	// 	for ($x=0; $x < count($jsonOb["locations"]); $x++) { 
	// 		$locationscount = count($jsonData["locations"]);
	// 		$jsonData["locations"][$locationscount] = $jsonOb["locations"][$x];
	// 	}
	// }

	foreach ($providers as $provider) {
		$jsonFile = file_get_contents(dirname(__DIR__)."/json/".$provider.".json");

		$jsonOb = json_decode($jsonFile,true);
		$jsonData["last_updated"] = $jsonOb["last_updated"];
		
		
		if (is_array($jsonOb["locations"])) {
			foreach ($jsonOb["locations"] as $location){
				$locationscount = count($jsonData["locations"]);
				$jsonData["locations"][$locationscount] = $location;
			}
		}
		
	}

	$jsonData["data_request"] = $v;
} else {
	$jsonData["last_updated"] = "N/A";
	$jsonData["data_request"] = $v;
}



echo json_encode($jsonData);

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
