<?php
header("Content-Type: application/json");
require("../php/database_r2.php");
$response = [];

$provider = json_decode(stripslashes(file_get_contents("php://input")),true);

$results = getChargerRecords("0_history",$provider["provider"]);

echo(json_encode($results));





		

?>


