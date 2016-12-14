<?php
//header("Content-Type: text/plain");
//require("database_r2.php");

$response = goFetch_hide("https://secure.chargeyourcar.org.uk/map-api-iframe");

if ($response !== false) {
	$error = false;

	//$myfile = fopen("cyc.src","w");
	//fwrite($myfile, $response);
	//fclose($myfile);

	//$response = file_get_contents("cyc.src");

	$response = substr($response,strpos($response,"autocomplete_init();") + 25);

	$response = substr($response,0,strpos($response,"// end document.ready"));

	$allStations = explode("var latlng = ",$response);
	$locations = [];

	foreach ($allStations as $key => $station) {
		if (strpos($station,"connectorStatus = []") === false) {
			array_splice($allStations,$key,1);
		}
	}
	foreach ($allStations as $key => $station) {
		$thisStation = [];
		$thisName = substr($station, strpos($station, "bubbleInfo_head") + 17);
		$thisName = substr($thisName, 0, strpos($thisName, "</div>"));

		$thisStation["name"] = $thisName;

		$thisLatLng = substr($station, strpos($station, "LatLng(") + 6);
		$thisLatLng = substr($thisLatLng, 0, strpos($thisLatLng, ")") + 1);
		$thisLatLng = str_replace("(", "", $thisLatLng);
		$thisLatLng = str_replace(")", "", $thisLatLng);
		$thisLatLng = str_replace("'", "", $thisLatLng);
		$thisLatLng = str_replace(" ", "", $thisLatLng);
		$thisLatLng = explode(",",$thisLatLng);

		$thisStation["lat"] = floatval($thisLatLng[0]);
		$thisStation["lng"] = floatval($thisLatLng[1]);

		$thisStation["provider"] = "CYC";
		$thisStation["provider_ocmid"] = 20;
		$thisStation["source"]["name"] = "ChargeYourCar";
		$thisStation["source"]["url"] = "http://chargeyourcar.org.uk/#map";

		$thisPostCode = substr($station, strpos($station, "bubbleItemContentFullWidth"));
		$thisPostCode = substr($thisPostCode,0 , strpos($thisPostCode, "</div></div>"));
		$thisPostCode = explode("<span>",$thisPostCode);
		$thisPostCode = $thisPostCode[count($thisPostCode)-1];
		$thisPostCode = substr($thisPostCode, 0,strpos($thisPostCode,"<"));

		$thisStation["postcode"] = $thisPostCode;

		$thisChargerID = substr($station, strpos($station, "<div class='bubbleItemContent'>") + 31);
		$thisChargerID = substr($thisChargerID, 0,strpos($thisChargerID,"</div>"));
		if (strpos($thisChargerID,"<b>")) {
			$thisChargerID = substr($thisChargerID, 0,strpos($thisChargerID,"<b>"));
		}
		$thisStation["chargerId"] = $thisChargerID;

		$thisTariff = substr($station, strpos($station, "Tariff"));
		$thisTariff = substr($thisTariff, strpos($thisTariff, "Content") + 9);
		$thisTariff = substr($thisTariff, 0,strpos($thisTariff,"</div>"));

		$thisTariff = mb_convert_encoding($thisTariff,"UTF-8");
		
		
		$thisStation["tariff"] = $thisTariff;

		$thisConnectors = substr($station, strpos($station, "var connectorsOutput") + 20);
		$thisConnectors = substr($thisConnectors,0, strpos($thisConnectors, "bubbleItemSubhead'>A"));
		$thisConnectors = explode("connectorStatus.push",$thisConnectors);

		foreach ($thisConnectors as $key => $singleConnector) {
			if (strpos($singleConnector,"Connector") === false){
				array_splice($thisConnectors,$key,1);
			}
		}

		$thisStation["connectors"] = [];

		foreach ($thisConnectors as $connectorIndex => $thisConnector) {
			$connector = [];
			$connectorData = explode("bubbleItemContainer",$thisConnector);

			foreach ($connectorData as $dataKey => $data) {
				if (strpos($data,"bubbleItemContent") === false) {
					array_splice($connectorData,$dataKey,1);
				}
			}

			$type = substr($connectorData[0],strpos($connectorData[0],"bubbleItemContent") + 20);
			$type = substr($type, 0, strpos($type,"</div>") - 1);
			$type = str_replace(["+","'"], "", $type);
			$type = trim($type);

			$status = substr($connectorData[1],strpos($connectorData[1],"bubbleItemContent") + 20);
			$status = substr($status, 0, strpos($status,"</div>") - 1);
			$status = str_replace(["+"," ","'"], "", $status);
			

			$updated = substr($connectorData[2],strpos($connectorData[2],"bubbleItemContent") + 19);
			$updated = substr($updated, 0, strpos($updated,"</div>"));
			$connector["updated"] = $updated;

			$power = substr($connectorData[3],strpos($connectorData[3],"bubbleItemContent") + 19);
			$power = substr($power, 0, strpos($power,"</div>"));
			$power = str_replace("kW", "", $power);
			$power = floatval($power);
			$connector["power"] = $power;

			switch($type){
				case "62196 Type 2":
					if ($power > 30){
						$connector["type"]["title"] = "AC (tethered)";
						$connector["type"]["id"] = 3;
					} else {
						$connector["type"]["title"] = "AC (socket)";
						$connector["type"]["id"] = 4;
					}
					break;

				case "BS1363 domestic 3 pin":
					$connector["type"]["title"] = "13A 3-Pin";
					$connector["type"]["id"] = 5;
					break;

				case "Combined Charging System":
					$connector["type"]["title"] = "CCS";
					$connector["type"]["id"] = 2;
					break;

				case "CHAdeMO":
					$connector["type"]["title"] = "CHAdeMO";
					$connector["type"]["id"] = 1;
					break;				
			}

			switch ($status) {
				case "OUT_OF_SERVICE":
					$connector["status"] = "offline";
					break;

				case "IDLE":
					$connector["status"] = "online";
					break;

				case "TRANSACTION_IN_PROGRESS":
					$connector["status"] = "occupied";
					break;

				case "UNKNOWN":
					$connector["status"] = "unknown";
					break;
			}

			if ($power > 20) {
				array_push($thisStation["connectors"],$connector);

				$sqlChargerOb = [];

				$sql_datetime = date("Y-m-d H:i:s");

				$sqlChargerOb["provider"] = "CYC";
				$sqlChargerOb["type"] = $connector["type"]["title"];
				$sqlChargerOb["name"] = $thisStation["name"];
				$sqlChargerOb["lat"] = $thisStation["lat"];
				$sqlChargerOb["lng"] = $thisStation["lng"];
				$sqlChargerOb["status"] = $connector["status"];
				$sqlChargerOb["connectorIndex"] = $connectorIndex;
				$sqlChargerOb["providerUniqueId"] = $thisStation["chargerId"];

				if ($power < 30) {
					//somethign with multiple low power AC sockets?
				}

				//echo"Connector: ". $sqlChargerOb["name"]." - ".$thisStation["chargerID"]." - ".$sqlChargerOb["lat"].",".$sqlChargerOb["lng"]." - ".$sqlChargerOb["connectorIndex"]." - ".$sqlChargerOb["type"].chr(10);
				$exists = checkChargerExists("0_status",$sqlChargerOb);
				//echo $exists.chr(10);
				if ($exists){
					
					updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchecked",$sql_datetime);
					$oldStatus = getChargerStateOrLastUpdate("0_status",$sqlChargerOb,"status")["status"];
					//echo $oldStatus." = ".$connector["status"].chr(10);

					if ( $oldStatus <> $connector["status"]){
						updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"status",$connector["status"]);
						updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchange",$sql_datetime);
						insertChargerRecordHistory("0_history",$sqlChargerOb,$oldStatus,$connector["status"],$sql_datetime);
					}

				} else {
					insertChargerRecord("0_status",$sqlChargerOb,$sql_datetime);
					insertChargerRecordHistory("0_history",$sqlChargerOb,"none",$connector["status"],$sql_datetime);
				}


			} elseif (!is_numeric($power)) {
				$error = true;
			} else {

			}
			
		}

		if ($thisStation["connectors"]){
			array_push($locations, $thisStation );
		}
		
	}

	

	//var_dump($allStations);
	$jsonObject["last_updated"] = date('j M H:i');
	$jsonObject["locations"] = $locations;

	if (count($jsonObject["locations"]) < 200 ){
		$error = true;
	}

	//var_dump($jsonObject);

	if (!$error) {
		$myfile = fopen(dirname(__DIR__)."/json/cyc.json","w");
		fwrite($myfile, json_encode($jsonObject));
		fclose($myfile);

		echo "CYC_direct: Success! ".count($jsonObject["locations"]).' chargers updated.'.chr(10);
	} else {
		echo 'CYC_direct: Update failed.'.chr(10);
	}
	
} else {
	echo 'CYC_direct: Update failed.'.chr(10);
}

// Custom function to use cookies.
function goFetch_hide($url, $params = null)
{
	// Our cookies.
	global $mmmCookies;
	// The cURL handle.
	$ch = curl_init($url);
	// If we got some POST params to use.
	if(is_array($params))
	{
		// Sort the params.
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
	}
	// Set a modern UA string.
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36"));
	// Follow redirects if we get them.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	// Return the output as a string instead of outputting it.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// How long to wait before we give up on Nissan.
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	// Where to save cookies when we're done.
	curl_setopt($ch, CURLOPT_COOKIEJAR, $mmmCookies);
	// Which cookies to use.
	curl_setopt($ch, CURLOPT_COOKIEFILE, $mmmCookies);
	// Execute the request.
	$response = curl_exec($ch);
	// If cURL returned an error.
	if(curl_error($ch) != ""){
        // TODO - The request failed. Do something.
        return false;
		
	}
	
	// Return the response.
	return $response;
}































		
?>
