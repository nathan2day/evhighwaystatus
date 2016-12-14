<?php
header("Content-Type: application/json");

require("../php/database_r2.php");
require("../php/PHPMailer/PHPMailerAutoload.php");
require("setup_mail.php");

ignore_user_abort(true);
set_time_limit(0);

if (!isset($_GET["send"])) {
	echo "Try with send!";
	exit();
}

$users = getActivatedBetaUsers(["test"=>true]);

//$users = getActivatedBetaUsers();



$subject = "A subject";
$message = "Some message";


for ($i=0; $i < count($users); $i++) { 
	echo $users[$i]["First_Name"]." ".$users[$i]["Last_Name"]." ".sendGeneralEmail([
		"First_Name" => $users[$i]["First_Name"],
		"Last_Name" => $users[$i]["Last_Name"],
		"email" => $users[$i]["Email_Address"],
		"uid" => $users[$i]["User_ID"],
		"subject" => $subject,
		"message" => $message
		
	]).chr(13);
	
}


?>
