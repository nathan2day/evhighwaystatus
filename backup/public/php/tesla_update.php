<?php

$jsonData = [];

ini_set('user_agent','NameOfAgent(evhighwaystatus.co.uk)');
$response = json_decode(goFetch("http://api.openchargemap.io/v2/poi/?output=json&countrycode=GB&operatorid=23&maxresults=5000"),true);

if (count($response) > 10){

	$jsonData["last_updated"] = date('j M H:i');
	$jsonData["locations"] = [];
	$count = 0;

	for ($i=0; $i < count($response) ; $i++) {
		updateOCMCharger($response[$i]);
		$highpower = false;

		// for ($x=0; $x < count($response[$i]["Connections"]); $x++) {
		// 	if (floatval($response[$i]["Connections"][$x]["PowerKW"]) > 40 ||
		// 		floatval($response[$i]["Connections"][$x]["LevelID"]) == 3     ){
		// 		$highpower = true;
		// 	}
		// }

		// if ($highpower) {

		// 	$jsonData["locations"][$count]["provider"] = "Tesla SC";
		// 	$jsonData["locations"][$count]["provider_openid"] = 23;
		// 	$jsonData["locations"][$count]["name"] = $response[$i]["AddressInfo"]["Title"];
		// 	$jsonData["locations"][$count]["lat"] = $response[$i]["AddressInfo"]["Latitude"];
		// 	$jsonData["locations"][$count]["lng"] = $response[$i]["AddressInfo"]["Longitude"];
		// 	$jsonData["locations"][$count]["postcode"] = $response[$i]["AddressInfo"]["Postcode"];
		// 	$jsonData["locations"][$count]["source"]["name"] = "OpenChargeMap"; 
		// 	$jsonData["locations"][$count]["source"]["url"] = "http://openchargemap.org/site/poi/details/".$response[$i]["ID"]; 

		// 	for ($x=0; $x < count($response[$i]["Connections"]); $x++) {

		// 		$jsonData["locations"][$count]["connectors"][$x]["power"] = 120;
				
		// 			switch(floatval($response[$i]["Connections"][$x]["StatusTypeID"])) {
		// 				case 50:
		// 					$jsonData["locations"][$count]["connectors"][$x]["status"] = "online";
		// 					break;
		// 				case 100:
		// 					$jsonData["locations"][$count]["connectors"][$x]["status"] = "offline";
		// 					break;
		// 				case 150:
		// 					$jsonData["locations"][$count]["connectors"][$x]["status"] = "planned";
		// 					break;
		// 				case 75:
		// 					$jsonData["locations"][$count]["connectors"][$x]["status"] = "unknown";
		// 					break;
		// 				default:
		// 					$jsonData["locations"][$count]["connectors"][$x]["status"] = "unknown";
		// 					break;
		// 			}

		// 			switch($response[$i]["Connections"][$x]["ConnectionTypeID"]) {
		// 				case 2:
		// 					$jsonData["locations"][$count]["connectors"][$x]["type"] = "CHAdeMO";
		// 					break;
		// 				case 3:
		// 					$jsonData["locations"][$count]["connectors"][$x]["type"] = "13A 3-Pin";
		// 					break;		
		// 				case 27:
		// 					$jsonData["locations"][$count]["connectors"][$x]["type"]["title"] = "Tesla SC";
		// 					$jsonData["locations"][$count]["connectors"][$x]["type"]["id"] = 7;
		// 					break;	
		// 				case 33:
		// 					$jsonData["locations"][$count]["connectors"][$x]["type"] = "CCS";
		// 					break;	
		// 				default:
		// 					$jsonData["locations"][$count]["connectors"][$x]["type"] = "unknown - ".$response[$i]["Connections"][$x]["ConnectionTypeID"];
		// 					break;	
		// 			}
		// 		$jsonData["locations"][$count]["connectors"][$x]["quantity"] = $response[$i]["Connections"][$x]["Quantity"];
		// 	}

		// 	$count++;
		// }		
			
	}

	// $myfile = fopen("../json/tesla.json","w");
	// fwrite($myfile, json_encode($jsonData));
	// fclose($myfile);

	echo "Tesla: Success! ".count($response).' chargers updated.'.chr(10);

} else {
	echo 'Tesla: Update failed.'.chr(10);
}

?>