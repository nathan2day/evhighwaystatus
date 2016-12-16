<?php


$jsonData = [];
// $myfile = fopen("../xml/cyc.xml","w");
// fwrite($myfile, file_get_contents("https://ce.corethree.net/Clients/ChargeYourCar/ListAllPoints"));
// fclose($myfile);


$response = json_decode(goFetch("http://api.openchargemap.io/v2/poi/?output=json&countrycode=GB&operatorid=20&maxresults=5000"),true);

if (count($response) > 500){

	$jsonData["last_updated"] = date('j M H:i');
	$jsonData["locations"] = [];
	$count = 0;

	for ($i = 0; $i < count($response) ; $i++) {
		updateOCMCharger($response[$i]);
		// $highpower = false;

		// for ($x=0; $x < count($response[$i]["Connections"]); $x++) {
		// 	if (floatval($response[$i]["Connections"][$x]["LevelID"]) == 3){
		// 		$highpower = true;
		// 	}

		// 	if (floatval($response[$i]["Connections"][$x]["PowerKW"]) == 22){
		// 		$highpower = true;
		// 	}
		// }
		
		// if ($highpower && $response[$i]["UsageTypeID"] !== 2) {
		// 	$cCount = 0;

		// 	$jsonData["locations"][$count]["provider"] = "CYC";
		// 	$jsonData["locations"][$count]["provider_openid"] = 20;
		// 	$jsonData["locations"][$count]["name"] = $response[$i]["AddressInfo"]["Title"];
		// 	$jsonData["locations"][$count]["lat"] = $response[$i]["AddressInfo"]["Latitude"];
		// 	$jsonData["locations"][$count]["lng"] = $response[$i]["AddressInfo"]["Longitude"];
		// 	$jsonData["locations"][$count]["postcode"] = $response[$i]["AddressInfo"]["Postcode"];
		// 	$jsonData["locations"][$count]["source"]["name"] = "OpenChargeMap";
		// 	$jsonData["locations"][$count]["source"]["url"] = "http://openchargemap.org/site/poi/details/".$response[$i]["ID"];

		// 	for ($x=0; $x < count($response[$i]["Connections"]); $x++) {

		// 		if (floatval($response[$i]["Connections"][$x]["LevelID"]) == 3 || floatval($response[$i]["Connections"][$x]["PowerKW"]) == 22 ){


		// 			$jsonData["locations"][$count]["connectors"][$cCount]["power"] = floatval($response[$i]["Connections"][$x]["PowerKW"]);

		// 			switch(floatval($response[$i]["Connections"][$x]["StatusTypeID"])) {
		// 				case 50:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["status"] = "online";
		// 					break;
		// 				case 100:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["status"] = "offline";
		// 					break;
		// 				case 150:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["status"] = "planned";
		// 					break;
		// 				case 75:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["status"] = "unknown";
		// 					break;
		// 				default:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["status"] = "unknown";
		// 					break;
		// 			}

		// 			switch($response[$i]["Connections"][$x]["ConnectionTypeID"]) {
		// 				case 2:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["title"] = "CHAdeMO";
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["id"] = 1;
		// 					break;
		// 				case 3:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["title"] = "13A 3-Pin";
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["id"] = 5;
		// 					break;	
		// 				case 1036:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["title"] = "AC (tethered)";
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["id"] = 3;
		// 					break;	
		// 				case 25:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["title"] = "AC (socket)";
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["id"] = 3;
		// 					break;	
		// 				case 33:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["title"] = "CCS";
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["id"] = 2;
		// 					break;	
		// 				default:
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["title"] = "unknown - ".$response[$i]["Connections"][$x]["ConnectionTypeID"];
		// 					$jsonData["locations"][$count]["connectors"][$cCount]["type"]["id"] = 0;
		// 					break;	
		// 			}
		// 			$jsonData["locations"][$count]["connectors"][$cCount]["quantity"] = $response[$i]["Connections"][$x]["Quantity"];
		// 			$cCount++;
		// 		}
		// 	}

		// 	$count++;
		// }		
			
	}

	// $myfile = fopen("../json/cyc.json","w");
	// fwrite($myfile, json_encode($jsonData));
	// fclose($myfile);

	echo "CYC: Success! ".count($response).' chargers updated.'.chr(10);
		
} else {
	echo "CYC: Update failed. {count($response) chargers returned.}".chr(10);
}

?>
