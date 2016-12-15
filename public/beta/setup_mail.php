<?php

function sendGeneralEmail($params){
	global $mail;

	$mailBefore = '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Demystifying Email Design</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic" rel="stylesheet" type="text/css">
</head>
<body style="margin: 0; padding: 0;">
 <table border="0" cellpadding="10px" cellspacing="0" width="100%"><tr><td style="font-family: "Open Sans", sans-serif;">';

   $mailAfter = '
	 </td>
	</tr>
 </table>
</body>
</html>';


	$mail->setFrom('beta@evhighwaystatus.co.uk', 'EVHighwayStatus');
	$mail->addAddress($params["email"],$params["First_Name"]." ".$params["Last_Name"]);     // Add a recipient

	$mail->isHTML(true);                                  // Set email format to HTML

	$bodyHTML = $mailBefore.$params["message"]."<br><br>Thanks,<br>EVHighwayStatus<br><br><a href=".'"https://evhighwaystatus.co.uk?uid='.$params["uid"].'&beta=true">Launch Beta Site</a>'.$mailAfter;

	$body = "";

	$mail->Subject = $params["subject"];
	$mail->Body    = $bodyHTML;
	$mail->AltBody = $body;

	$result = $mail->send(); //true is success

	$mail->ClearAllRecipients(); // Clear all recipient types(to, bcc, cc).

	return $result;
}

function sendUrlEmail($params){
	global $mail;

	$mailBefore = '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Demystifying Email Design</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic" rel="stylesheet" type="text/css">
</head>
<body style="margin: 0; padding: 0;">
 <table border="0" cellpadding="10px" cellspacing="0" width="100%"><tr><td style="font-family: "Open Sans", sans-serif;">';

   $mailAfter = '
	 </td>
	</tr>
 </table>
</body>
</html>';

	$mail->setFrom('beta@evhighwaystatus.co.uk', 'EVHighwayStatus');
	$mail->addAddress($params["email"],$params["First_Name"]." ".$params["Last_Name"]);     // Add a recipient
	$mail->addBCC('andrew.d.lees@gmail.com');

	$mail->isHTML(true);                                  // Set email format to HTML

	$bodyHTML = $mailBefore."Hi ".$params["First_Name"]."<br><br>Now you're all registered, here's your URL for our Beta version of the site.<br><br>Your unique URL is: <a href=".'"https://evhighwaystatus.co.uk?uid='.$params["uid"].'&beta=true">https://evhighwaystatus.co.uk?uid='.$params["uid"]."&beta=true</a><br><br>Using this URL to access our site will ensure you're provided with the Beta scripts instead of our live ones. Exciting! As it works around sessions, should you ever wish to revert to the 'normal' site you just need to close the browser window and navigate to our site using the standard URL.<br><br>".'For the time being, whilst we work on a bug tracking system, please email issues, suggestions, results and anything else you fancy to us at <a href="mailto:contact@evhighwaystatus.co.uk">contact@evhighwaystatus.co.uk</a>.<br><br>Thanks again for your support.<br><br>EVHighwayStatus'.$mailAfter;

	$body = "";

	$mail->Subject = "Your Beta access URL";
	$mail->Body    = $bodyHTML;
	$mail->AltBody = $body;

	$mail->send(); //true is success
	$mail->ClearAllRecipients(); // Clear all recipient types(to, bcc, cc).
}

function sendJoiningMail($params){

	global $mail;

	$mailBefore = '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Demystifying Email Design</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic" rel="stylesheet" type="text/css">
</head>
<body style="margin: 0; padding: 0;">
 <table border="0" cellpadding="10px" cellspacing="0" width="100%"><tr><td style="font-family: "Open Sans", sans-serif;">';

   $mailAfter = '
	 </td>
	</tr>
 </table>
</body>
</html>';

	$mail->setFrom('beta@evhighwaystatus.co.uk', 'EVHighwayStatus');
	$mail->addAddress($params["email"],$params["First_Name"]." ".$params["Last_Name"]);     // Add a recipient
	//$mail->addBCC('andrew.d.lees@gmail.com');

	$mail->isHTML(true);                                  // Set email format to HTML

	if ($params["ios"] === 'true') {
		$bodyHTML = $mailBefore."Hi ".$params["First_Name"]."<br><br>Thanks for registering for our iOS App beta, we look forward to your feedback!<br><br>To ensure we only contact those who request it, we just need to confirm your email address. To do this, please click <a href=".'"https://EVHighwayStatus.co.uk/beta/validate.php?ios=true&uid='.$params["uid"].'">here</a>.<br><br>Alternatively, you can paste the following into your browser:<br>https://EVHighwayStatus.co.uk/beta/validate.php?ios=true&uid='.$params["uid"]."<br><br>If you're received this email and have never heard of us, you don't need to do anything, we remove all unconfirmed emails.<br><br>Thanks<br>EVHighwayStatus<br>".$mailAfter;
		$mail->Subject = "iOS App TestFlight Confirmation";
	} else {
		$bodyHTML = $mailBefore."Hi ".$params["First_Name"]."<br><br>Thanks for registering for our Beta program, we look forward to your feedback!<br><br>To ensure we only contact those who request it, we just need to confirm your email address. To do this, please click <a href=".'"https://EVHighwayStatus.co.uk/beta/validate.php?ios=false&uid='.$params["uid"].'">here</a>.<br><br>Alternatively, you can paste the following into your browser:<br>https://EVHighwayStatus.co.uk/beta/validate.php?ios=false&uid='.$params["uid"]."<br><br>If you're received this email and have never heard of us, you don't need to do anything, we remove all unconfirmed emails.<br><br>Thanks<br>EVHighwayStatus<br>".$mailAfter;
		$mail->Subject = "Beta program confirmation";

	}

	$mail->Body    = $bodyHTML;
	$mail->AltBody = $body;

	$sendStatus = $mail->send(); //true is success 

	$mail->ClearAllRecipients(); // Clear all recipient types(to, bcc, cc).

	return $sendStatus;
}

$mail = new PHPMailer;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = '10.168.1.70';  				  // Specify main and backup SMTP servers
// $mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'beta@evhighwaystatus.co.uk';       // SMTP username
$mail->Password = 'ThisIsTheBetaAccount';             // SMTP password
//$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 25;                                    // TCP port to connect to

?>