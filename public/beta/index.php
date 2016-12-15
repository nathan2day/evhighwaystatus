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
	
		<meta charset="text/html; utf-8"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

		<title>EVHighwayStatus - Our beta program registration.</title>
		<meta name="description" content="Join our beta program to get access to pre-release updates and help us improve features before final release. Help make us great!"/>

		<!-- Twitter Card -->

		<meta property="twitter:card" content="summary_large_image"/>
		<meta property="twitter:url" content="https://evhighwaystatus.co.uk/beta"/>
		<meta property="twitter:site" content="evhighwaymap"/>
		<meta property="twitter:description" content="Help make EVHighwayStatus great! Join our beta program to be part of our development."/>
		<meta property="twitter:title" content="EVHighwayStatus - Our beta program"/>
		<meta property="twitter:image" content="https://evhighwaystatus.co.uk/img/car-wide.jpg"/>
		<meta property="twitter:image:alt" content="EVHighwayStatus Car Icon"/>

		<!-- Open Graph -->

		<meta property="og:site_name" content="EVHighwayStatus"/>
		<meta property="og:type" content="website"/>
		<meta property="og:url" content="https://evhighwaystatus.co.uk/beta"/>
		<meta property="og:description" content="Help make EVHighwayStatus great! Join our beta program to be part of our development."/>
		<meta property="og:title" content="EVHighwayStatus - Our beta program"/>
		<meta property="og:image" content="https://evhighwaystatus.co.uk/img/car-wide.jpg"/>
		<meta property="og:image:alt" content="EVHighwayStatus Car Icon"/>

		<!-- Facebook -->

		<meta property="fb:app_id" content="260883924295945"/>


	<link rel="shortcut icon" href="../img/favicon.ico">
	<link rel="apple-touch-icon" href="../img/apple-icon-152x152.png">
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic' rel='stylesheet' type='text/css'>
	<script src="../js/sweetalert.min.js?v=1"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script src='https://www.google.com/recaptcha/api.js'></script>
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
			text-align: justify;
		}
		.registration-subtitle{
			width: calc(100% - 2em);
			padding: 0 1em;
			text-align: justify;
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
			margin: 20px 1em 15px 1em;
    		font-size: 1.2em;
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
		<h3 class="registration-title">EVHighwayStatus Beta Program</h3>
		<h4 class="registration-subtitle">We need your help!</h4>
		<p>Enter your details below to register for our Beta program. Once registered, you'll be notified via email when features become available for Beta release.</p>
		<p>You'll also get access to our pre-release site to try out any new features and report bugs.</p>
		<p>We won't use your email for anything else other than to notify you when new features are available. You can unsubscribe at any time by contacting us <a href="mailto:contact@evhighwaystatus.co.uk?subject=Beta%20program%20unsubscribe">here</a>.</p>
		<form class="registration-form">
			<div class="input-group">
				<label for "first-name">First name:</label>
				<input type="text" id="first-name" name="first-name" class="form-input">
			</div>
			
			<div class="input-group">
				<label for "last-name">Last name:</label>
				<input type="text" id="last-name" name="last-name" class="form-input">
			</div>

			<div class="input-group">
				<label for "email-address">Email:</label>
				<input type="text" autocapitalize="off"   id="email-address" name="email-address" class="form-input">
			</div>

			<div class="input-group">
				<label for "email-address-repeat">Repeat email:</label>
				<input type="text" autocapitalize="off"   id="email-address-repeat" name="email-address-repeat" class="form-input">
			</div>

			<div class="g-recaptcha" data-sitekey="6Lde-yETAAAAAO4NiI36pnODaCbbLZUrHyHFJ9t_"></div>

			<button type="submit">Register</button>

		</form>

	</div>
	
</body>
</html>

<script>

$(document).ready(function(){

	$(".registration-form").submit(function(){
		event.preventDefault();
		var error = false;

		$(".form-input").removeClass("error");

		if ($(".form-input[name=first-name]").val() === "") {
			$(".form-input[name=first-name]").addClass("error");
			error = true;
		}
		if ($(".form-input[name=last-name]").val() === "") {
			$(".form-input[name=last-name]").addClass("error");
			error = true;
		}
		if ($(".form-input[name=email-address]").val() !== $(".form-input[name=email-address-repeat]").val() ||
			$(".form-input[name=email-address]").val() === "" || $(".form-input[name=email-address-repeat]").val() === "") {
			$(".form-input[name=email-address]").addClass("error");
			$(".form-input[name=email-address-repeat]").addClass("error");
			error = true;
		}	

		if (error) {
			return;
		}

		var data = {
			firstName: $(".form-input[name=first-name]").val(),
			lastName: $(".form-input[name=last-name]").val(),
			email: $(".form-input[name=email-address]").val(),
			response: grecaptcha.getResponse()
		};

		$.post("beta_registration.php?ios=false",JSON.stringify(data),function(data){
			$(".registration-form span").remove();
			grecaptcha.reset();
			var response = "";
			var aClass = ""
			if (!data.uservalidation) {
				response = "reCAPTCHA failed..are you a robot?";
				aClass = "response-false";
				
			} else {

				if (data.errors) {
					if (data.errors.indexOf("email-invalid") > -1){
						response = "Sorry, looks like that email isn't valid.";
						aClass = "response-false";
					} else if (data.errors.indexOf("email-used") > -1){
						response = "Sorry, that email's already registered.";
						aClass = "response-false";
					} else if (data.errors.indexOf("email-add-failed") > -1){
						response = "Sorry, that email's already registered.";
						aClass = "response-false";
					} else {
						response = "Sorry, something went wrong.";
						aClass = "response-false";
					}
				} else {
					response = "Thanks! Welcome aboard. Please check your inbox. We may be in your junk folder..";
					
					aClass = "response-true";

					
					$(".input-group").hide();
					$(".g-recaptcha").hide();
					$(".registration-form button").hide();
				}
				
			}

			$(".input-group").eq(3).after($("<span>",{class: aClass}).append(response));

		});

			
	});
	

});

</script>