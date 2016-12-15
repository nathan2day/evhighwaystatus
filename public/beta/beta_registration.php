<?php
header("Content-Type: application/json");

require("../php/database_r2.php");
require("../php/PHPMailer/PHPMailerAutoload.php");
require("setup_mail.php");

// The cookie jar to use across requests.
$mmmCookies = tempnam(sys_get_temp_dir(), 'carwings-csv-import');

$formdata = json_decode(stripslashes(file_get_contents("php://input")),true);

$url = "https://www.google.com/recaptcha/api/siteverify";	
$secret = "6Lde-yETAAAAAMrn2v6Uj7gu2WF5yqf-f5lV-YWu";
$response = $formdata["response"];
$remoteip = $_SERVER["HTTP_CF_CONNECTING_IP"];
//$remoteip = $_SERVER["REMOTE_ADDR"];
$googleResponse = json_decode(goFetch($url,array("secret"=> $secret, "response" => $response, "remoteip" => $remoteip)),true);

if ($googleResponse["success"] == false){
	$data["uservalidation"] = false;
	$data["fail_data"] = $googleResponse;
	$data["ip_sent"] = $remoteip;
} else {
	$data["uservalidation"] = true;
	$data["ip"] = $remoteip;
}

if ($data["uservalidation"] == true) {
	$email = $formdata["email"];

	// Remove all illegal characters from email
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);

	// Validate e-mail
	if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	    
	    //check email hasn't been registered before

		$emailExists = checkEmailExists([
    		"ios" => $_GET["ios"],
    		"email" => $email
	    ]);

	    if (!$emailExists) {

	    	$bytes = openssl_random_pseudo_bytes(20);
	    	$userid = print_r(bin2hex($bytes),true);
	
	    	$addUser = addBetaUser([
	    		"ios" => $_GET["ios"],
	    		"userid" => $userid,
	    		"firstName" => ucfirst($formdata["firstName"]),
	    		"lastName" => ucfirst($formdata["lastName"]),
	    		"email" => $formdata["email"]
	    	]);
	    	

	    	if ($addUser) {
	    		//confirmation email
	    		while (!$status) {
		    		$status = sendJoiningMail([
		    			"ios" => $_GET["ios"],
						"First_Name" => ucfirst($formdata["firstName"]),
						"Last_Name" => ucfirst($formdata["lastName"]),
						"email" => $email,
						"uid" => $userid
					]);	
					sleep(2);
	    		};
	    		

	    	} else {
	    		$data["errors"][count($data["errors"])] = "email-add-fail";
	    	}

	    } else {
	    	$data["errors"][count($data["errors"])] = "email-used";
	    }


	} else {
	    $data["errors"][count($data["errors"])] = "email-invalid";
	}
}


echo json_encode($data);




$conn->close();

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