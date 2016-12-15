<?php
header("Cache-Control: no-cache, must-revalidate, max-age=0 no-store");

if ($_SERVER["HTTP_X_FORWARDED_PROTO"] === "http" && strpos($_SERVER['HTTP_HOST'],'eta.') === false) {
	$redirect = "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	header("Location: ".$redirect);
	exit();
}

?>
<!doctype html>
<html>

<head>
	<title>EV Highway Status - Beta program registration.</title>
	<meta name="description" content="Join our Beta program to get access to pre-release updates and help us improve features before final release."/>
	<meta charset="text/html; utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<link rel="shortcut icon" href="../img/favicon.ico">
	<link rel="apple-touch-icon" href="../img/apple-icon-152x152.png">
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic' rel='stylesheet' type='text/css'>
	<script src="../js/sweetalert.min.js?v=1"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<style>
		body{
			font-family: "Open Sans", sans-serif;
			margin: 0;
			font-size: 15px;
		}
		.beta-program{
			width: 70%;
			min-width: 320px;
			max-width: 500px;
			margin: 1em auto;
			border-style: solid;
		    border-color: gray;
		    border-radius: 10px;
		    border-width: 2px;
		    text-align: center;
		}
		.beta-program p{
			width: calc(100% - 2em);
			padding: 0 1em;
			
		}
		.registration-subtitle{
			width: calc(100% - 2em);
			padding: 0 1em;
			font-size: 21px;
    		margin: 0;	
		}
		.input-group{
			display: block;
    		width: calc(100% - 2em);
    		font-family: inherit;
    		margin: 0.5em 0;
    		padding: 0 1em;
    		text-align: left;
		}
		.input-group:nth-of-type(4){
			margin-bottom: 16px;
		}
		.input-group label{
			display: block;
			width: 100%;			
			vertical-align: middle;
			padding: 5px 0; 
		}
		.input-group input{
			display: block;
			width: 100%;
			padding: 5px;
			font-size: inherit;
			vertical-align: middle;
			box-sizing: border-box;
		}
		button{
			padding: 0.5em 2.5em;
			line-height: 2em;
		    border-radius: 5px;
		    border-style: solid;
		    border-color: gray;
		    border-width: 1px;
		    font-family: inherit;
		    margin: 16px;
		    font-size: inherit;
		}
		@media all and (min-device-width: 600px) {
			button:hover{
				background-color: gray;
				cursor: pointer;
			}
		}		
		.error{
			border-color: red;
		}
		.response-false{
			color: red;
		}
		.response-true{
			color: green;
		}
		.response-true,.response-false{
			margin-bottom: 16px;
			display: block;
		}
		.g-recaptcha{
			margin: 0;			
		}
		div.g-recaptcha {
 		 	margin: 0 auto;
 		 	margin-top: 2px;
  			width: 304px;
		}
		@media screen and (max-device-width: 600px), screen and (device-width: 320px), screen and (device-width: 360px) {
			.beta-program{
				width: 100%;
				margin: 0;
				border-style: none;
			}
		}

	</style>
	
</head>

<body>
	
	<div class="beta-program">
		<h3 class="registration-title">Email Validation</h3>
		<?php
			require("../php/PHPMailer/PHPMailerAutoload.php");
			require("../php/database_r2.php");
			require("setup_mail.php");
			
			$subVar = "
			<p>";
			$result = checkEmailValidatedState([
				"user_id" => $_GET["uid"],
				"ios" => $_GET["ios"],
			]);

			if ($result === "no"){
				$update = updateEmailValidatedState([
					"user_id" => $_GET["uid"],
					"ios" => $_GET["ios"],
				]);
				if ($update){
					$titleVar = '<h4 class="registration-subtitle response-true">';
					$titleVar .= "Success!";

					if ($_GET["ios"] === 'true'){
						$subVar .= "Thanks for confirming your address. We'll add you to the list and you should receive an invite from Apple on your iOS device soon..";

					} else {
						$subVar .= "Thanks for confirming your address. We'll be in touch when we have features available for pre-release Beta testing. In the mean time, happy EVing!";

						//send URL email
						$user = getSpecificBetaUserData(["user_id" => $_GET["uid"]])[0];

						sendUrlEmail([
							"First_Name" => $user["First_Name"],
							"Last_Name" => $user["Last_Name"],
							"email" => $user["Email_Address"],
							"uid" => $user["User_ID"]
						]);
					}					

				} else {
					$titleVar = '<h4 class="registration-subtitle response-false">';
					$titleVar .= "Sorry..";
					$subVar .= '..looks like something went wrong. Please contact <a href="mailto:contact@evhighwaystatus.co.uk">contact@evhighwaystatus.co.uk</a>'." and we'll try get this bottomed out.";
				}
			} elseif ($result === "yes"){
				$titleVar = '<h4 class="registration-subtitle response-true">';
				$titleVar .= "Already confirmed!";
				$subVar .= '..looks like this address has already been validated.';
			} else {
				$titleVar = '<h4 class="registration-subtitle response-false">';
				$titleVar .= "We can't find you";
				$subVar .= "No record for this email address - you may have left it too long and it's expired. ".'Please <a href="https://evhighwaystatus.co.uk/beta">re-register</a> or contact <a href="mailto:contact@evhighwaystatus.co.uk">contact@evhighwaystatus.co.uk</a>.';
			}
			$conn->close();
			$titleVar .= "</h4>";
			$subVar .= "</p>";
			echo $titleVar;
			echo $subVar;



		?>
		

	</div>
	
</body>
</html>

<script>

// $(document).ready(function(){

// 	$(".registration-form").submit(function(){
// 		event.preventDefault();
// 		var error = false;

// 		$(".form-input").removeClass("error");

// 		if ($(".form-input[name=first-name]").val() === "") {
// 			$(".form-input[name=first-name]").addClass("error");
// 			error = true;
// 		}
// 		if ($(".form-input[name=last-name]").val() === "") {
// 			$(".form-input[name=last-name]").addClass("error");
// 			error = true;
// 		}
// 		if ($(".form-input[name=email-address]").val() !== $(".form-input[name=email-address-repeat]").val() ||
// 			$(".form-input[name=email-address]").val() === "" || $(".form-input[name=email-address-repeat]").val() === "") {
// 			$(".form-input[name=email-address]").addClass("error");
// 			$(".form-input[name=email-address-repeat]").addClass("error");
// 			error = true;
// 		}	

// 		if (error) {
// 			return;
// 		}

// 		var data = {
// 			firstName: $(".form-input[name=first-name]").val(),
// 			lastName: $(".form-input[name=last-name]").val(),
// 			email: $(".form-input[name=email-address]").val(),
// 			response: grecaptcha.getResponse()
// 		};

// 		$.post("beta_registration.php",JSON.stringify(data),function(data){
// 			$(".registration-form span").remove();
// 			grecaptcha.reset();
// 			var response = "";
// 			var aClass = ""
// 			if (!data.uservalidation) {
// 				response = "reCAPTCHA failed..are you a robot?";
// 				aClass = "response-false";
				
// 			} else {

// 				if (data.errors) {
// 					if (data.errors.indexOf("email-invalid") > -1){
// 						response = "Sorry, looks like that email isn't valid.";
// 						aClass = "response-false";
// 					} else if (data.errors.indexOf("email-used") > -1){
// 						response = "Sorry, that email's already registered.";
// 						aClass = "response-false";
// 					} else if (data.errors.indexOf("email-add-failed") > -1){
// 						response = "Sorry, that email's already registered.";
// 						aClass = "response-false";
// 					} else {
// 						response = "Sorry, something went wrong.";
// 						aClass = "response-false";
// 					}
// 				} else {
// 					response = "Thanks! Welcome aboard. Please check your inbox.";
// 					response = "Thanks! Welcome aboard. Please check your inbox.";
// 					aClass = "response-true";
// 				}
				
// 			}

// 			$(".input-group").eq(3).after($("<span>",{class: aClass}).append(response));
// 		});

			
// 	});
	

// });

</script>