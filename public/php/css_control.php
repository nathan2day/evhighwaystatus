<?php
require_once("php/Mobile_Detect.php");
$detect = new Mobile_Detect;

if ( $detect->isMobile() || $detect->isTablet() ) {
	$_SESSION["device"] = "portable";
} else {
	$_SESSION["device"] = "desktop";
}

if ($_SESSION["beta"]){
$timestamp = date('His',filemtime('css/style.beta.css'));
echo '<link rel="stylesheet" href="css/style.beta.css?uid=' . $timestamp . '">
	';	
} else {
$timestamp = date('His',filemtime('css/style.css'));
echo '<link rel="stylesheet" href="css/style.css?uid=' . $timestamp . '">
	';
}



// $timestamp = date('His',filemtime('css/sweetalert.css'));
// echo '<link rel="stylesheet" type="text/css" href="css/sweetalert.css?uid=' . $timestamp . '">
// 	';

// $timestamp = date('His',filemtime('css/style_mobile.css'));
// echo '<link media="screen and (max-device-width: 600px), screen and (device-width: 320px), screen and (device-width: 360px)" rel="stylesheet" href="css/style_mobile.css?uid=' . $timestamp . '">
// ';

if ($_SESSION["device"] == "portable"){
	$timestamp = date('His',filemtime('css/style_portable.css'));
echo '	<link rel="stylesheet" type="text/css" href="css/style_portable.css?uid=' . $timestamp . '">
';
}

?>