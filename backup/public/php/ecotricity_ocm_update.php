<?php

$jsonData = [];

ini_set('user_agent','NameOfAgent(evhighwaystatus.co.uk)');
$response = json_decode(goFetch("http://api.openchargemap.io/v2/poi/?output=json&countrycode=GB&operatorid=24&maxresults=5000"),true);

if (count($response) > 10){

	for ($i=0; $i < count($response) ; $i++) {
		updateOCMCharger($response[$i]);	
	}


	echo "Eco OCM: Success! ".count($response).' chargers updated.'.chr(10);

} else {
	echo 'Eco OCM: failed.'.chr(10);
}

?>