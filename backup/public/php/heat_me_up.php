<?php
header("Content-type: application/json");

$auth = false;
session_start();

if(isset($_SERVER["HTTP_XAPPAUTH"]) &&
   $_SERVER["HTTP_XAPPAUTH"] === "8;iLY3AZ1m7,?[pUKM0!+E7h44;u2W81dWl<(mf85kevN0J-MN^V6P1F47VTE77") {
 	$_SESSION["validated"] = true;
}

$users = ["2f17a7b9c32259b5a7e160308c68a1ed","1f17a7b9c32259b5a7e160308c68a1ed"];

if (isset($_POST["key"])){
	if (array_search($_POST["key"],$users) !== false ){
		$auth = true;
		if (!isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] !== "https")){
			echo "Non-encrypted connections unsupported - HTTPS only.";
			exit();
		}
	}
}

if (!isset($_SESSION["validated"]) && !$auth){
	echo "Contact admin@evhighwaystatus.co.uk for access.";
	exit();
}

// The cookie jar to use across requests.
$mmmCookies = tempnam(sys_get_temp_dir(), 'carwings-csv-import');
$status = [];

$action = $_POST["action"];
$username = $_POST["username"];
$password = $_POST["password"];

$status["request"] = $action;

$user = carwingsLogin($username,$password);

if ($user !== ""){
	//login success
	$status["status"] = "login_success";
	$status["user_id"] = $user;

	switch ($action){
		case "battery":
			carwingsAction("https://youplus.nissan.co.uk/GB/en/YouPlus/private/carwings/flashdata.cwupdatedata.xml");
			break;
		case "ac_off":
			carwingsAction("https://youplus.nissan.co.uk/GB/en/YouPlus/private/carwings/flashdata.cwturnoffacdata.xml");
			break;
		case "ac_on":
			carwingsAction("https://youplus.nissan.co.uk/GB/en/YouPlus/private/carwings/flashdata.cwturnonacdata.xml");
			break;
		case "start_charge":
			carwingsAction("https://youplus.nissan.co.uk/GB/en/YouPlus/private/carwings/flashdata.cwturnonchargingdata.xml");
			break;
		default:
			break;
	}

} else {
	//login fail
	$status["status"] = "login_fail";
}

echo json_encode($status);

//functions

function carwingsAction($url){

	global $status;
	global $action;
	$count = 0;
	$response = goFetch($url);
	sleep(2);
	$response = goFetch($url);

	do {
		$response_2 = goFetch($url);
		sleep(1);
		$count++;
	} while ($response == $response_2 && $count < 30);

	$xml = simplexml_load_string($response_2,null, LIBXML_NOCDATA);

	if (strpos((string)$xml->messageid,"succeed") !== false){
		$status["status"] = "action_success";
		$status["request_time"] = $count;
		if ($action == "battery"){
			$leafInfo = goFetch("https://youplus.nissan.co.uk/content/GB/en/YouPlus/private/carwings/flashdata.cwalldataxml.xml");
			$xml = simplexml_load_string($leafInfo,null, LIBXML_NOCDATA);
			$status["leaf_info"]["range"]["ac_on"] = (float)$xml->alldata->distance_ac_on;
			$status["leaf_info"]["range"]["ac_off"] = (float)$xml->alldata->distance_ac_off;
			$status["leaf_info"]["bars"] = (float)$xml->alldata->actual_charge;
		}
	} else {
		$status["request_time"] = 0;

		if ($count == 30){
			$status["status"]= "request_timeout";
		} else {
			$status["status"] = "carwings_update_fail";
			$status["carwings_response"] = (string)$xml->messageid;
		}
	}
}

function carwingsLogin($username,$password){
	global $status;

	// Login to Nissan. UPDATE: URL updated to HTTPS as they finally fixed that.
	$response = goFetch("https://youplus.nissan.co.uk/GB/en/YouPlus.html/j_security_check", array(
	"j_validate" => true,
	"j_username" => $username,
	"j_password" => $password,
	"_charset_" => "utf8"
	));

	// Grabbed from Fiddler. You have to call these or nothing works. UPDATE: Added second link which is new.
	$response = goFetch("https://youplus.nissan.co.uk/content/GB/en/YouPlus/private/home.processafterlogin.html");
	$response = goFetch("https://youplus.nissan.co.uk/GB/en/YouPlus/private/carwings/flashdata.testusersession.html");

	if ($response == 'true'){
		$response = goFetch("https://youplus.nissan.co.uk/content/GB/en/YouPlus/private/home.processafterlogin.html");
		$user_id = substr($response,strpos($response,"Welcome"));
		$user_id = substr($user_id,0,strpos($user_id,"&"));
		$user_id = substr($user_id,8);
	} else {
		$user_id = "";
	}
	return $user_id;		
}


// Custom function to use cookies.
function goFetch($url, $params = null)
{
	// Our cookies.
	global $mmmCookies;
	// The cURL handle.
	$ch = curl_init($url);
	// If we got some POST params to use.
	if(is_array($params)) {
		
		// Sort the params.
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
	}
	// Set a modern UA string.
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2540.0 Safari/537.36"));
	// Follow redirects if we get them.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	// Return the output as a string instead of outputting it.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// How long to wait before we give up on Nissan.
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	// Where to save cookies when we're done.
	curl_setopt($ch, CURLOPT_COOKIEJAR, $mmmCookies);
	// Which cookies to use.
	curl_setopt($ch, CURLOPT_COOKIEFILE, $mmmCookies);
	// Execute the request.
	$response = curl_exec($ch);
	// If cURL returned an error.
	if(curl_error($ch) != ""){
        // TODO - The request failed. Do something.
        $status["status"]= "request_timeout";
        echo json_encode($status);
		exit();
	}
	
	// Return the response from Nissan.
	return $response;
}
?>