<?php

$jsonData = [];


$response = goFetch("https://www.teslamotors.com/en_GB/findus#");


$response = substr($response, strpos($response,"var location_data =") + 20);
$response = substr($response,0,strpos($response,"var production = true;") - 5);

$jsonData = json_decode($response,true);

$tesla_sc["last_updated"] = date('j M H:i');
$tesla_sc["locations"] = [];

$tesla["last_updated"] = date('j M H:i');
$tesla["locations"] = [];

$tesla_dc["last_updated"] = date('j M H:i');
$tesla_dc["locations"] = [];

$teslaOrig = [];

foreach ($jsonData as $key => $location) {
	if ($location["country"] == "United Kingdom"){
		//get all charger locations
		if (array_search("supercharger", $location["location_type"]) !== false || array_search("destination charger",$location["location_type"]) !== false ){
			
			$temp["name"] = $location["title"];
			$temp["provider"] = 'Tesla';
			$temp["lat"] = floatval($location["latitude"]);	
			$temp["lng"] = floatval($location["longitude"]);
			$temp["postcode"] = $location["postal_code"];
			$temp["source"]["name"] = "Tesla"; 
			$temp["source"]["url"] = "https://www.teslamotors.com/en_GB/findus/";
			$temo["provider_openid"] = 23;
			$temp["connections"] = [];

			//find out how many superchargers, if it has them
			if (array_search("supercharger", $location["location_type"]) !== false){
				$chargerCount = substr($location["chargers"],strpos($location["chargers"],"Superchargers") - 2,1);

				$connector = count($temp["connections"]);

				$temp["connectors"][$connector]["power"] = 120;
				$temp["connectors"][$connector]["quantity"] = intval($chargerCount);
				$temp["connectors"][$connector]["status"] = "online";
				$temp["connectors"][$connector]["type"]["title"] = "Tesla SC";
				$temp["connectors"][$connector]["type"]["id"] = 7;

				$teslaOrig[count($teslaOrig)] = $location;

				array_push($tesla["locations"],$temp);

			}

			//find out how many destination chargers, if it has them
			if (array_search("destination charger", $location["location_type"]) !== false){
				$chargerCount = substr($location["chargers"],strpos($location["chargers"],"Tesla Connector") - 2,1);
				$power = substr($location["chargers"],strpos($location["chargers"],"up to") + 6 ,20);
				$power = substr($power, 0, strpos($power,"kW"));

				$connector = count($temp["connections"]);

				$temp["connectors"][$connector]["power"] = round(floatval($power),0,PHP_ROUND_HALF_DOWN);
				$temp["connectors"][$connector]["quantity"] = intval($chargerCount);
				$temp["connectors"][$connector]["status"] = "online";
				$temp["connectors"][$connector]["type"]["title"] = "Tesla Dest.";
				$temp["connectors"][$connector]["type"]["id"] = 7;

				if ($temp["connectors"][$connector]["power"] > 5) {
					array_push($tesla["locations"],$temp);
				}

				$teslaOrig[count($teslaOrig)] = $location;

			}

			//$tesla[count($tesla)] = $temp;
		}
	}
}

if (count($tesla["locations"]) > 10){
	$myfile = fopen(dirname(__DIR__)."/json/tesla_sc.json","w");
	fwrite($myfile, json_encode($tesla_sc));
	fclose($myfile);

	$myfile = fopen(dirname(__DIR__)."/json/tesla_dc.json","w");
	fwrite($myfile, json_encode($tesla_dc));
	fclose($myfile);

	$myfile = fopen(dirname(__DIR__)."/json/tesla.json","w");
	fwrite($myfile, json_encode($tesla));
	fclose($myfile);	

	echo "Tesla-new: Success! ".count($tesla["locations"]).' chargers updated.'.chr(10);
} else {
	echo "Tesla-new: Failed!";
}

?>
