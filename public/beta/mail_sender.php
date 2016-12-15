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



$subject = "We're getting there!";
$message = "Afternoon folks<br><br>Just a quick one to let you know we've updated our beta site.<br><br>We've updated the setup wizard to allow you to choose 'not listed' and specify your own battery capacity, and then get walked through choosing connectors and networks.<br><br>In addition, the menu has been expanded to include the advanced route settings for efficiency, this can now be set directly in the menu.<br><br>We've rewritten the autocomplete on the input fields of the route planner, so this should be more responsive (especially on mobile devices), and it'll allow us to customise it further down the line.<br><br>Pin popups have now been redesigned, as they were starting to look a little out of touch with the rest of the layout - let us know what you think.<br><br>We want to get this new site out to users as soon as possible, so please have a quick play and let us know if you come across any show-stoppers.<br><br>As you know, we have social 'badges' on the site which take you straight to our Facebook page, Google+ community or Twitter account for any feedback.<br><br>All feedback is good feedback!";


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