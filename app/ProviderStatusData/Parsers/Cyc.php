<?php namespace App\ProviderStatusData\Parsers;

use App\ProviderStatusData\Interfaces\Parser;

class Cyc implements Parser
{
	/**
	 * Take an HTTP response, and parse it to an array of
	 * of location objects with connectors.
	 *
	 * @param $HttpResponse
	 * @return array Locations and Connectors
	 */
	public function parse($HttpResponse)
	{
		$response = $HttpResponse;
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
					array_push($thisStation["connectors"],(object) $connector);
				}

			}

			if ($thisStation["connectors"]){
				array_push($locations,(object) $thisStation );
			}

		}

		// If we have a 'good' number of locations, return them.
		return count($locations) > 200 ? $locations :false;
	}
}