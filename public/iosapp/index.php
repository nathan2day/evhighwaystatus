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

	<title>EVHighwayStatus - iOS App TestFlight Registration.</title>
	<meta name="description" content="Want EVHighwayStatus on your iOS device? Register to help us evaluate our new iOS app!"/>

	<!-- Twitter Card -->

	<meta property="twitter:card" content="summary_large_image"/>
	<meta property="twitter:url" content="https://evhighwaystatus.co.uk/iosapp"/>
	<meta property="twitter:site" content="evhighwaymap"/>
	<meta property="twitter:description" content="We're looking for testers! Register here to get involved."/>
	<meta property="twitter:title" content="EVHighwayStatus iOS App (beta)"/>
	<meta property="twitter:image" content="https://evhighwaystatus.co.uk/img/car-wide-ios.jpg"/>
	<meta property="twitter:image:alt" content="EVHighwayStatus Car Icon"/>

	<!-- Open Graph -->

	<meta property="og:site_name" content="EVHighwayStatus"/>
	<meta property="og:type" content="website"/>
	<meta property="og:url" content="https://evhighwaystatus.co.uk/iosapp"/>
	<meta property="og:description" content="We're looking for testers! Register here to get involved."/>
	<meta property="og:title" content="EVHighwayStatus iOS App (beta)"/>
	<meta property="og:image" content="https://evhighwaystatus.co.uk/img/car-wide-ios.jpg"/>
	<meta property="og:image:alt" content="EVHighwayStatus Car Icon"/>

	<!-- Facebook -->

	<meta property="fb:app_id" content="260883924295945"/>


	<!-- Icons -->

	<link rel="apple-touch-icon-precomposed" sizes="57x57" href="https://evhighwaystatus.co.uk/img/apple-touch-icon-57x57.png" />
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="https://evhighwaystatus.co.uk/img/apple-touch-icon-114x114.png" />
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="https://evhighwaystatus.co.uk/img/apple-touch-icon-72x72.png" />
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="https://evhighwaystatus.co.uk/img/apple-touch-icon-144x144.png" />
	<link rel="apple-touch-icon-precomposed" sizes="120x120" href="https://evhighwaystatus.co.uk/img/apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon-precomposed" sizes="152x152" href="https://evhighwaystatus.co.uk/img/apple-touch-icon-152x152.png" />
	<link rel="icon" type="image/png" href="https://evhighwaystatus.co.uk/img/favicon-32x32.png" sizes="32x32" />
	<link rel="icon" type="image/png" href="https://evhighwaystatus.co.uk/img/favicon-16x16.png" sizes="16x16" />
	<meta name="application-name" content="EVHighwayStatus"/>
	<meta name="msapplication-TileColor" content="#FFFFFF" />
	<meta name="msapplication-TileImage" content="https://evhighwaystatus.co.uk/img/mstile-144x144.png" />

	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic' rel='stylesheet' type='text/css'>
	
	<script src="../js/sweetalert.min.js?v=1"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<script src="https://use.fontawesome.com/112f0c1659.js"></script>
	<link rel="stylesheet" href="../css/w3.css"/>
	<style>
		body{
			font-family: "Open Sans", sans-serif;
			margin: 0;
			font-size: 15px;
		}
		.beta-program{
			width: 70%;
			min-width: 364px;
			max-width: 500px;
			margin: 1em auto;
			border-style: solid;
		    border-color: gray;
		    border-radius: 10px;
		    border-width: 2px;
		    text-align: center;
		}
		.beta-program p{
			text-align: justify;
		}
		.registration-subtitle{
			text-align: justify;
		}
		.input-group{
			display: block;
    		font-family: inherit;
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
		.button-cont{
			position: relative;
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
		#loading{
			position: absolute;
		    left: calc(50% - 12px);
		    top: calc(50% - 11px);
		    font-size: 22px;
		    display: none;
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
		.g-recaptcha>div {
    		width: initial !important;
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
	
	<div class="beta-program w3-container">
		<h3 class="registration-title">EVHighwayStatus iOS TestFlight Registration</h3>
		<h4 class="registration-subtitle">We need your help (again)!</h4>
		<p>Due to popular demand, we've widened our development horizons and created an iOS App version of EVHighwayStatus. Following an inital limited alpha release, we hope to have addressed many of the early issues, but we now want to open up testing to even more users to see what you all think. It's being done for you after all.</p>
		<p>Once registered, we'll be able to add you to the external testers list, but it may take up to 24 hours for your invitation email from Apple to arrive. Please be patient. </p>
		<p>As usual, we won't use your email for anything else other than to add you to the Apple TestFlight system. You can unsubscribe at any time by contacting us <a href="mailto:contact@evhighwaystatus.co.uk?subject=iOS%20TestFlight%20unsubscribe">here</a>.</p>
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

			<div class="button-cont">
				<button type="submit">Register</button>
				<i id="loading" class="fa fa-refresh fa-spin fa-fw "></i>
			</div>


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

		$("button").css({"visibility":"hidden"});
		$("#loading").show();

		$.ajax({
			method: 'post',
			url: '../beta/beta_registration.php?ios=true',
			data: JSON.stringify(data)
		}).done(function(data){
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
					response = "Thanks! Welcome aboard. We've sent you a confirmation email, once confirmed, we'll get you added to the list.";
					
					aClass = "response-true";

					
					$(".input-group").hide();
					$(".g-recaptcha").hide();
					$(".registration-form button").hide();
				}
				
			}

			$(".input-group").eq(3).after($("<span>",{class: aClass}).append(response));

		}).fail(function(a,b,c){
			var response = a;
		}).always(function(){
			$("button").css({"visibility":"visible"});;
			$("#loading").hide();
		})
	
	});
	

});

</script>