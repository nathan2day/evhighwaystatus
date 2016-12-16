<?php 
$j = [];
$j_new = [];


	

$result = ecotricityUpdateOld();

if ($result == false){
	echo 'Eco: Update failed.'.chr(10);
} else {
	echo "Eco: Success. Wrote ".$result." bytes.".chr(10);
}

function ecotricityUpdateOld(){
	global $j;
	//define user agent for requests
	ini_set('user_agent','NameOfAgent(Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36)');

	//create our DOMDocument to work with the HTML
	$htmlpage = new DOMDocument();

	//grab the status map html from Ecotricity.
	$sourcestring = file_get_contents('http://www.ecotricity.co.uk/for-the-road/our-electric-highway/');//

	ecotricityUpdateNew($sourcestring);
	
	return true;
	//only progress if file_get_contents is successful
	if ($sourcestring === FALSE) {
		return false;
	} else {
		//suppress DOMDocument warnings
		libxml_use_internal_errors(true);

		//load html string from website into DOM doc
		$htmlpage->loadHTML($sourcestring);

		//create DOMXPath for Class queries
		$xpath = new DOMXPath($htmlpage);

		//create XML skeleton for status data
		$statusxml = new DOMDocument('1.0','UTF-8');
		$statusroot = $statusxml->createElement("Root");
		$statusroot = $statusxml->appendChild($statusroot);
		$updateTime = $statusxml->createElement("Last_Updated",date('j M H:i'));
		$updateTime = $statusroot->appendChild($updateTime);

		$j["last_updated"] = date('j M H:i');
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

		$j_new["locations"] = [];

		//if not created, add table for this provider
		if (checkTableExists("0_status") === false){
			dbAddTable("0_status");
		}

		//for each marker, append status data from DOMNodeList objects to XML root 
		for ($i=0; $i < $names->length; $i++) { 

			$c = [];

			//append parent node for this charger
			$currentCharger = $statusxml->createElement("Charger");
			$currentCharger = $statusroot->appendChild($currentCharger);

			//add child nodes for status information
			//name
			$currentCharger->appendChild($statusxml->createElement("Name",$names->item($i)->nodeValue));

			$c["name"] = $names->item($i)->nodeValue;

			//charger type
			$typenode = $statusxml->createElement("Type");
			$typenode->appendChild($statusxml->createTextNode($types->item($i)->nodeValue));
			$currentCharger->appendChild($typenode);

			$c["type"] = $types->item($i)->nodeValue;
			$c["postcode"] = $postCodes->item($i)->nodeValue;
			$c["county"] = $counties->item($i)->nodeValue;

			//connector - split by children first due to </br> html element 
			$connectornodes = $connectors->item($i)->childNodes;
			$connname = "";

			//connector - concatenate with space any non-blank children nodeValues
			foreach ($connectornodes as $node) {
				if ($node->nodeValue != "") {
					$connname = $connname.$node->nodeValue." ";
				}
			}

			//connector - trim the trailing space and append
			$connnametrimmed = trim($connname);
			$connnode = $statusxml->createElement("Connector");
			$connnode->appendChild($statusxml->createTextNode($connnametrimmed));
			$currentCharger->appendChild($connnode);

			$c["connector"] = $connnametrimmed;

			//latitude and longitude
			$currentCharger->appendChild($statusxml->createElement("Lat",$lats->item($i)->nodeValue));
			$currentCharger->appendChild($statusxml->createElement("Lng",$lngs->item($i)->nodeValue));

			$c["lat"] = floatval($lats->item($i)->nodeValue);
			$c["lng"] = floatval($lngs->item($i)->nodeValue);

			//online and offline statuses
			$currentCharger->appendChild($statusxml->createElement("Offline",$offlines->item($i)->nodeValue));
			$currentCharger->appendChild($statusxml->createElement("Planned",$planneds->item($i)->nodeValue));

			$c["offline"] = intval($offlines->item($i)->nodeValue);
			$c["planned"] = intval($planneds->item($i)->nodeValue);

			if ($c["offline"] == 1) {
				$state = "offline";
			} elseif ($c["planned"] == 1){
				$state = "planned";
			} else {
				$state = "unknown";
			}



			$t = isNewEntryOld($j_new["locations"],$c);

			$sqlChargerOb = [];

			if ($t > -1){
				$connectorcount = count($j_new["locations"][$t]["connectors"]);
				
				if (strrpos($c["connector"],"CCS") !== false){
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"] = "CCS";
					$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 50;
				} elseif (strrpos($c["connector"],"AC") !== false && strrpos($c["connector"],"DC") !== false ){
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"] = "CHAdeMO AC";
					$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = "50/43";
				} elseif (strrpos($c["connector"],"DC") !== false ){
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"] = "CHAdeMO";
					$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 50;
				} elseif (strrpos($c["connector"],"AC") !== false ){
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"] = "AC Medium";
					if (strrpos($c["connector"],"7") !== false){
						$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 7;
					} else {
						$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 22;
					}
				} else {
					$j_new["locations"][$t]["connectors"][$connectorcount]["type"] = $c["connector"];
					$j_new["locations"][$t]["connectors"][$connectorcount]["power"] = 0;
				}

				$j_new["locations"][$t]["connectors"][$connectorcount]["status"] = $state;
				$j_new["locations"][$t]["connectors"][$connectorcount]["socket"] = $c["connector"];


				$sqlChargerOb["type"] = $j_new["locations"][$t]["connectors"][$connectorcount]["type"];

			} else {
				$locationcount = count($j_new["locations"]);
				$j_new["locations"][$locationcount]["name"] = $c["name"];
				$j_new["locations"][$locationcount]["provider"] = "Ecotricity";

				$j_new["locations"][$locationcount]["lat"] = $c["lat"];
				$j_new["locations"][$locationcount]["lng"] = $c["lng"];

				$j_new["locations"][$locationcount]["postcode"] = $c["postcode"];
				
				if (strrpos($c["connector"],"CCS") !== false){
					$j_new["locations"][$locationcount]["connectors"][0]["type"] = "CCS";
					$j_new["locations"][$locationcount]["connectors"][0]["power"] = 50;
				} elseif (strrpos($c["connector"],"AC") !== false && strrpos($c["connector"],"DC") !== false ){
					$j_new["locations"][$locationcount]["connectors"][0]["type"] = "CHAdeMO AC";
					$j_new["locations"][$locationcount]["connectors"][0]["power"] = "50/43";
				} elseif (strrpos($c["connector"],"DC") !== false ){
					$j_new["locations"][$locationcount]["connectors"][0]["type"] = "CHAdeMO";
					$j_new["locations"][$locationcount]["connectors"][0]["power"] = 50;
				} elseif (strrpos($c["connector"],"AC") !== false ){
					$j_new["locations"][$locationcount]["connectors"][0]["type"] = "AC Medium";
					if (strrpos($c["connector"],"7") !== false){
						$j_new["locations"][$locationcount]["connectors"][0]["power"] = 7;
					} else {
						$j_new["locations"][$locationcount]["connectors"][0]["power"] = 22;
					}
					
				} else {
					$j_new["locations"][$locationcount]["connectors"][0]["type"] = $c["connector"];
					$j_new["locations"][$locationcount]["connectors"][0]["power"] = 0;
				}
				
				$j_new["locations"][$locationcount]["connectors"][0]["status"] = $state;
				$j_new["locations"][$locationcount]["connectors"][0]["socket"] = $c["connector"];

				$sqlChargerOb["type"] = $j_new["locations"][$locationcount]["connectors"][0]["type"];
				
				$j["chargers"][$i]=$c;
				
			}

			$sql_datetime = date("Y-m-d H:i:s");

			$sqlChargerOb["provider"] = "Ecotricity";
			$sqlChargerOb["name"] = $c["name"];
			$sqlChargerOb["lat"] = $c["lat"];
			$sqlChargerOb["lng"] = $c["lng"];
			$sqlChargerOb["status"] = $state;

			if (checkChargerExists("0_status",$sqlChargerOb)){
				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchecked",$sql_datetime);
				$oldStatus = getChargerStateOrLastUpdate("0_status",$sqlChargerOb,"status")["status"];
				if ($oldStatus <> $state){
					updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"status",$state);
					updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchange",$sql_datetime);
					insertChargerRecordHistory("0_ecotricity",$sqlChargerOb,$oldStatus,$state,$sql_datetime);
				}
				

				$oldName = getChargerStateOrLastUpdate("0_status",$sqlChargerOb,"name")["name"];
				if ($oldName <> $sqlChargerOb["name"]){
					updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"name",$sqlChargerOb["name"]);
				}

				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"postcode",$c["postcode"]);
				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"county",$c["county"]);

				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchecked",$sql_datetime);

			} else {
				insertChargerRecord("0_status",$sqlChargerOb,$sql_datetime);
				insertChargerRecordHistory("0_ecotricity",$sqlChargerOb,"none",'added',$sql_datetime);

			}

		}


		return $statusxml->save(dirname(__DIR__)."/xml/status.xml");
	
	}
}

function isNewEntryOld($locations,$c){
	
		foreach ($locations as $key => $location) {
			if (($location["lat"] == $c["lat"]) && ($location["lng"] == $c["lng"])) {
				return $key; 
			}
		}
	return -1;
	
}



function ecotricityUpdateNew($sourceString){

	global $connection;

	//$statuses = $connection->get("statuses/home_timeline", ["count" => 2, "exclude_replies" => true]);
	//$statuses = $connection->get("statuses/home_timeline", ["count" => 3, "exclude_replies" => true]);
	//var_dump($statuses);

	//$post_test = $connection->post("statuses/update", ["status" => "This is just a test."]);

	//define user agent for requests
	ini_set('user_agent','NameOfAgent(Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36)');

	//create our DOMDocument to work with the HTML
	$htmlpage = new DOMDocument();

	//grab the status map html from Ecotricity.
	$sourcestring = $sourceString;//

	//only progress if file_get_contents is successful
	if ($sourcestring === FALSE) {
		return false;
	} else {
		//suppress DOMDocument warnings
		libxml_use_internal_errors(true);

		//load html string from website into DOM doc
		$htmlpage->loadHTML($sourcestring);

		//create DOMXPath for Class queries
		$xpath = new DOMXPath($htmlpage);

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

			$t = isNewEntry($j_new["locations"],$c);
	
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

				$sqlChargerOb = [];

				$sql_datetime = date("Y-m-d H:i:s");

				$sqlChargerOb["provider"] = "Ecotricity";
				$sqlChargerOb["name"] = $c["name"];
				$sqlChargerOb["lat"] = $c["lat"];
				$sqlChargerOb["lng"] = $c["lng"];
				$sqlChargerOb["type"] = $j_new["locations"][$t]["connectors"][$connectorcount]["type"]["title"];
				$sqlChargerOb["status"] = $state;

				if (checkChargerExists("0_status",$sqlChargerOb)){
				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"connector_count",floatval($thisCon[1]));
				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchecked",$sql_datetime);
				$oldStatus = getChargerStateOrLastUpdate("0_status",$sqlChargerOb,"status")["status"];

				if ($oldStatus <> $state){
					updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"status",$state);
					updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchange",$sql_datetime);

					insertChargerRecordHistory("0_ecotricity",$sqlChargerOb,$oldStatus,$state,$sql_datetime);
				}

				$oldName = getChargerStateOrLastUpdate("0_status",$sqlChargerOb,"name")["name"];
				if ($oldName <> $sqlChargerOb["name"]){
					updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"name",$sqlChargerOb["name"]);
				}

				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"postcode",$c["postcode"]);
				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"county",$c["county"]);

				updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchecked",$sql_datetime);
			} else {
				insertChargerRecord("0_status",$sqlChargerOb,$sql_datetime);

				insertChargerRecordHistory("0_ecotricity",$sqlChargerOb,"none",'added',$sql_datetime);
			}

			}
		}

		$myFile = fopen(dirname(__DIR__)."/json/ecotricity.json","w");

		fwrite($myFile,json_encode($j_new));
		fclose($myFile); 

		return count($j_new["locations"]);
	}
}

function isNewEntry($locations,$c){
	
		foreach ($locations as $key => $location) {
			if (($location["lat"] == $c["lat"]) && ($location["lng"] == $c["lng"])) {
				return $key; 
				echo "hit";
			}
		}
	return count($locations);
	
}

?>
