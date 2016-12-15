<?php
header("Content-Type: application/json");


session_start();

if (isset($_SERVER["HTTP_ORIGIN"]) && strpos($_SERVER['HTTP_HOST'],"evhighwaystatus.co.uk") !== FALSE) {
	$origin = $_SERVER["HTTP_ORIGIN"];
	header("Access-Control-Allow-Origin: $origin");
	$_SESSION["validated"] = true;
}
	
// //Are we coming from HTTP page?
// if (isset($_COOKIE["PHPSESSID"])) {
// 	session_id($_COOKIE["PHPSESSID"]);
// }

if(isset($_SERVER["HTTP_XAPPAUTH"]) &&
   $_SERVER["HTTP_XAPPAUTH"] === "8;iLY3AZ1m7,?[pUKM0!+E7h44;u2W81dWl<(mf85kevN0J-MN^V6P1F47VTE77") {
 	$_SESSION["validated"] = true;
}

if (!isset($_SESSION["validated"])){
	echo "Contact admin@evhighwaystatus.co.uk for access.".$_COOKIE["PHPSESSID"];
	exit();
}



$request = json_decode(stripslashes(file_get_contents("php://input")),true);

$key = "AIzaSyB9_vfqgqGrIwOeY3tN9tHztRVEU_J7JwM";

$url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=".urlencode($request["input"])."&location=".$request["location"]."&radius=".$request["radius"]."&key=".$key;

//$request["url"] = $url;

$results = goFetch($url);

echo($results);






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


