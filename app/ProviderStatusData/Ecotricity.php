<?php namespace App\ProviderStatusData;

use App\ProviderStatusData\Interfaces\Parser;

class Ecotricity implements Parser
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
		//create our DOMDocument to work with the HTML
		$htmlpage = new \DOMDocument();

		//suppress DOMDocument warnings
		libxml_use_internal_errors(true);

		//load html string from website into DOM doc
		$htmlpage->loadHTML($HttpResponse);

		//create DOMXPath for Class queries
		$xpath = new \DOMXPath($htmlpage);

		$j_new["last_updated"] = date('j M H:i');

		//use XPath queries to pull elements by class name
		$names = $xpath->query('//div[contains(@class, "name")]');
		$types = $xpath->query('//div[contains(@class, "type")]');
		$connectors = $xpath->query('//div[contains(@class, "connector")]');
		$lats = $xpath->query('//div[contains(@class, "lat")]');
		$lngs = $xpath->query('//div[contains(@class, "lng")]');
		$offlines = $xpath->query('//div[contains(@class, "offline")]');
		$planneds = $xpath->query('//div[contains(@class, "planned")]');
		$counties = $xpath->query('//div[contains(@class, "county")]');
		$postCodes = $xpath->query('//div[contains(@class, "postcode")]');

		//has load been successful?
		if ($names->length < 100) {
			//failed to get data
			return false;
		}

		//initialise json object for locations
		$j_new["locations"] = [];

		//for each marker, append status data from DOMNodeList objects to XML root
		for ($i=0; $i < $names->length; $i++) {
			//this charger
			$c = [];
			//name
			$c["name"] = $names->item($i)->nodeValue;
			//charger type
			$c["type"] = $types->item($i)->nodeValue;
			$c["postcode"] = $postCodes->item($i)->nodeValue;
			$c["county"] = $counties->item($i)->nodeValue;
			//connector - split by children first due to </br> html element
			$connArr = [];
			$count = 0;
			foreach ($connectors->item($i)->childNodes as $node) {
				if ($node->nodeValue != "") {
					if (strpos($node->nodeValue,"DC/AC") !== false){
						$connArr[$count] = str_replace("DC/AC","DC",$node->nodeValue);
					} else{
						$connArr[$count] = $node->nodeValue;
					}
					$count++;
				}
			}
			//latitude and longitude
			$c["lat"] = floatval($lats->item($i)->nodeValue);
			$c["lng"] = floatval($lngs->item($i)->nodeValue);
			//online and offline statuses
			$c["offline"] = intval($offlines->item($i)->nodeValue);
			$c["planned"] = intval($planneds->item($i)->nodeValue);
			if ($c["offline"] == 1) {
				$state = "offline";
			} elseif ($c["planned"] == 1){
				$state = "planned";
			} else {
				$state = "unknown";

			}

			$t = $this->isNewEntry($j_new["locations"],$c);

			foreach ($connArr as $connector) {
				$mult = count($connArr) > 1;

				$j_new["locations"][$t]["provider"] = "Ecotricity";
				$j_new["locations"][$t]["provider_openid"] = 24;
				$j_new["locations"][$t]["name"] = $c["name"];
				$j_new["locations"][$t]["lat"] = $c["lat"];
				$j_new["locations"][$t]["lng"] = $c["lng"];
				$j_new["locations"][$t]["postcode"] = $c["postcode"];
				$j_new["locations"][$t]["source"]["name"] = "Ecotricity";
				$j_new["locations"][$t]["source"]["url"] = "http://www.ecotricity.co.uk/for-the-road/our-electric-highway";
				$thisCon = explode(":",$connector);
				$connectorcount = isset($j_new["locations"][$t]["connectors"]) ? count($j_new["locations"][$t]["connectors"]) : 0 ;
				if ($thisCon[0] == "CCS"){
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["title"] = "CCS";
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["id"] = 2;
					$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 50;
				} elseif ($thisCon[0] == "DC"){
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["title"] = "CHAdeMO";
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["id"] = 1;
					$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 50;
				} elseif ($thisCon[0] == "AC"){
					if ($c["type"] == "AC medium charge"){
						$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["title"] = "AC (socket)";
						$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["id"] = 4;
						$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 22;
					} else {
						$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["title"] = "AC (tethered)";
						$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["id"] = 3;
						$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 43;
					}
				} elseif ($thisCon[0] == "AC (7kw)"){
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["title"] = "AC (socket)";
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["id"] = 4;
					$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 7;
				} else {
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["title"] = "Unknown";
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"]["id"] = 0;
					$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 0;
				}
				$j_new["locations"][$t]["connectors"][$connectorcount]["quantity"] = floatval($thisCon[1]);
				$j_new["locations"][$t]["connectors"][$connectorcount]["status"] = $state;
				$j_new["locations"][$t]["connectors"][$connectorcount]["isDual"] = $mult;

				$j_new["locations"][$t]["connectors"] = array_map(function($value) {
					return (object) $value;
				},$j_new["locations"][$t]["connectors"]);
			}
		}

		return array_map(function($value) {
			return (object) $value;
		},$j_new["locations"]);
	}

	private function isNewEntry($locations, $c)
	{
		foreach ($locations as $key => $location) {
			if (($location["lat"] == $c["lat"]) && ($location["lng"] == $c["lng"])) {
				return $key;
			}
		}
		return count($locations);
	}
}