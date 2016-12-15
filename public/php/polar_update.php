<?php 

$result = polarUpdate();

$j=[];

if ($result == false){
	echo 'Polar: Update failed.'.chr(10);
} else {
	$posts = $result->posts;

	$j["last_updated"] = date('j M H:i');
	$j["locations"] = [];

	// $myfile = fopen("../json/polar.json","w");
	// fwrite($myfile, json_encode($posts));
	// fclose($myfile);

	$count=0;

	$sqlChargerOb = [];

	//if not created, add table for this provider
	if (checkTableExists("0_status") === false){
		dbAddTable("0_status");
	}
	
	for ($x=0; $x < count($posts) ; $x++) { //for all of the posts

		$highpower = FALSE;

		for ($i=0; $i < count($posts[$x]->Sockets); $i++) { 
			if ($posts[$x]->Sockets[$i]->KW == 43 || $posts[$x]->Sockets[$i]->KW == 50) {
				$highpower = TRUE;
			}
			if ($posts[$x]->Sockets[$i]->KW == 43 || $posts[$x]->Sockets[$i]->KW == 22) {
				$highpower = TRUE;
			}
		}

		if ($highpower){

			$c = [];

			$c["name"] = $posts[$x]->Address;
			$c["postcode"] = $posts[$x]->Postcode;
			$c["lat"] = $posts[$x]->Latitude;
			$c["lng"] = $posts[$x]->Longitude;

			$c["sockets"] = [];	

			for ($i = 0; $i < count($posts[$x]->Sockets) ; $i++) { 
				$s = [];

				switch ($posts[$x]->Sockets[$i]->Rating) {
					case 'Rapid-Chademo':
						$s["type"]["title"] = "CHAdeMO";
						$s["type"]["id"] = 1;
						break;
					case 'Rapid-AC':
						$s["type"]["title"] = "AC (tethered)";
						$s["type"]["id"] = 3;
						break;
					case 'Rapid-Combo':
						$s["type"]["title"] = "CCS";
						$s["type"]["id"] = 2;
						break;
					case 'Type2':
						$s["type"]["title"] = "AC (socket)";
						$s["type"]["id"] = 4;
						break;	
					default:
						$s["type"]["title"] = $posts[$x]->Sockets[$i]->Rating;
						$s["type"]["id"] = 0;
						break;		
				}

				switch ($posts[$x]->Sockets[$i]->Status) {
					case 0:
						$s["status"] = "online";
						break;
					case 1:
						$s["status"] ="occupied";
						break;
					case 2:
						$s["status"] = "offline";
						break;	
					default:
						$s["status"] = "unknown";
						break;	
				}

				$s["power"] = $posts[$x]->Sockets[$i]->KW;


				$sql_datetime = date("Y-m-d H:i:s");

				$sqlChargerOb["provider"] = "Polar";
				$sqlChargerOb["type"] = $s["type"]["title"];
				$sqlChargerOb["name"] = $c["name"];
				$sqlChargerOb["lat"] = $c["lat"];
				$sqlChargerOb["lng"] = $c["lng"];
				$sqlChargerOb["status"] = $s["status"];

				if (checkChargerExists("0_status",$sqlChargerOb)){
					
					updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchecked",$sql_datetime);
					$oldStatus = getChargerStateOrLastUpdate("0_status",$sqlChargerOb,"status")["status"];

					if ( $oldStatus <> $s["status"]){
						updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"status",$s["status"]);
						updateChargerStateOrLastUpdate("0_status",$sqlChargerOb,"lastchange",$sql_datetime);

						insertChargerRecordHistory("0_history",$sqlChargerOb,$oldStatus,$s["status"],$sql_datetime);
					}
				} else {
					insertChargerRecord("0_status",$sqlChargerOb,$sql_datetime);

					insertChargerRecordHistory("0_history",$sqlChargerOb,"none",'added',$sql_datetime);
				}

				$c["sockets"][$i] = $s;
			}

			$j["locations"][$count]["name"] = $c["name"];
			$j["locations"][$count]["provider"] = "Polar";
			$j["locations"][$count]["provider_openid"] = 32;
			$j["locations"][$count]["lat"] = floatval($c["lat"]);
			$j["locations"][$count]["lng"] = floatval($c["lng"]);
			$j["locations"][$count]["postcode"] = $c["postcode"];
			$j["locations"][$count]["source"]["url"] = "http://polar-network.com/map/";
			$j["locations"][$count]["source"]["name"] = "Polar";
			$j["locations"][$count]["connectors"] = $c["sockets"];

			$count++;
		}	
	}

	$myfile = fopen(dirname(__DIR__)."/json/polar.json","w");
	fwrite($myfile, json_encode($j));
	fclose($myfile);

	echo "Polar: Success! ".count($j["locations"]).' chargers updated.'.chr(10);

}

function polarUpdate(){

	//define user agent for requests
	ini_set('user_agent','NameOfAgent(Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36)');

	//grab the Polar post data JSON.
	$sourcestring = file_get_contents('http://www.polar-network.com/ajax/posts/');//

	//only progress if file_get_contents is successful
	if ($sourcestring === FALSE) {
		return false;
	} else {
		$json = json_decode($sourcestring);
		if (count($json->posts) < 10){
			return false;
		} else {
			return $json;
		}
	}
}

?>
