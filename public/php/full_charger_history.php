<?php
header("Content-Type: application/json");
require(dirname(__DIR__)."/php/database_r2.php");

session_start();

if (isset($_SERVER["HTTP_XAPPAUTH"]) &&
    $_SERVER["HTTP_XAPPAUTH"] === "8;iLY3AZ1m7,?[pUKM0!+E7h44;u2W81dWl<(mf85kevN0J-MN^V6P1F47VTE77") {
 	$_SESSION["validated"] = true;
}

if (!isset($_SESSION["validated"])){
	echo "Contact admin@evhighwaystatus.co.uk for access.";
	exit();
}

$location = json_decode(stripslashes(file_get_contents("php://input")),true);
$location["limit"] = 8000;

$results = getChargerHistory($location);

echo(json_encode($results));





		

?>


