<?php
header("Content-Type: text/plain");

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

require("database_r2.php");

include_once("cpg_update.php");
sleep(1);
include_once("cyc_update.php");
sleep(1);
include_once("e_car_update.php");
sleep(1);
include_once("esb_update.php");
sleep(1);
include_once("ecotricity_update.php");
include_once("polar_update.php");
include_once("tesla_update.php");
sleep(1);
include_once("nissan_update.php");
include_once("podpoint_update.php");
sleep(1);
include_once("ecotricity_ocm_update.php");
sleep(1);
include_once("engenie_update.php");
sleep(1);
include_once("polar_ocm_update.php");
include_once("tesla_update_new.php");
include_once("cyc_update_direct.php");

$conn->close();


?>