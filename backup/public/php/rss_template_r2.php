<?php
require("../../php/database_r2.php");
header("Content-type: text/xml");

$file_name = substr($_SERVER['SCRIPT_FILENAME'],strrpos($_SERVER['SCRIPT_FILENAME'],"/")+1,6);
$dir_name = substr($_SERVER['SCRIPT_FILENAME'],0,strrpos($_SERVER['SCRIPT_FILENAME'],"/"));
$dir_name = substr($dir_name,strrpos($dir_name,"/")+1,6);
$dbtable = $dir_name."_".$file_name;

if (strpos($_SERVER['HTTP_HOST'],'eta.')>0) {$dbtable = "b".$dbtable;}
$chargersToShowUpdate=[];

$chargersToShowUpdate = getChargerRecords($dbtable,"all");

$sql_datetime = date("Y-m-d H:i:s");


$dataToEcho = 
'<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
  <title>Electric Highway Update</title>
  <link>http://evhighwaystatus.co.uk</link>
  <description>Your update on the Electric Highway</description>
  <language>en</language>
  <image>
  	  <title>Electric Highway Update</title>
      <url>http://evhighwaystatus.co.uk/img/apple-icon-152x152.png</url>
      <link>http://evhighwaystatus.co.uk</link>
  </image>';


foreach ($chargersToShowUpdate as $chargerToUpdate) {

	if (strrpos($chargerToUpdate["name"],",") > 0) {
		$shortname = explode(",",$chargerToUpdate["name"]);
		$shortname = $shortname[0].", ".$shortname[1];
	} else {
		$shortname = $chargerToUpdate["name"];
	}

	$shortname = htmlspecialchars($shortname);

	//check for status changed

	$latestStatus = getChargerStateOrLastUpdate("0_status",$chargerToUpdate,"status");

	if ($latestStatus === false) {
		$latestStatus = " cannot be found";
		updateChargerStateOrLastUpdate($dbtable,$chargerToUpdate,"status","not found");
		updateChargerStateOrLastUpdate($dbtable,$chargerToUpdate,"lastchange",$sql_datetime);		
	} else {
		 if ($latestStatus["status"] <> $chargerToUpdate["status"]){

			//status has changed since last execution, so update feed table

			updateChargerStateOrLastUpdate($dbtable,$chargerToUpdate,"status",$latestStatus["status"]);
			updateChargerStateOrLastUpdate($dbtable,$chargerToUpdate,"lastchange",$sql_datetime);
		}

		$latestStatus = ' is '.$latestStatus["status"];
	}

	//decode SQL datetime to string for URL in feed item

	$lastUpdated = getChargerStateOrLastUpdate($dbtable,$chargerToUpdate,"lastchange")["lastchange"];
	$lastUpdated = strtotime($lastUpdated);
	$lastUpdated = date("YmdHis",$lastUpdated);

	//update last checked
	updateChargerStateOrLastUpdate($dbtable,$chargerToUpdate,"lastchecked",$sql_datetime);

	//Add item to the echo string

	$dataToEcho = $dataToEcho.
 			'<item>
			<title>'.$shortname.' '.$chargerToUpdate["type"].$latestStatus.'</title>
			<link>http://evhighwaystatus.co.uk?lat='.$chargerToUpdate["lat"].'&amp;lng='.$chargerToUpdate["lng"].'&amp;con='.urlencode($chargerToUpdate["type"]).'&amp;upd='.$lastUpdated.'</link>
			<description></description>
			</item>';


	// if (!$charger_found) {

	// 		$laststate = getRecords($dbtable,$charger,"state");
	// 		$laststate = $laststate["state"];

	// 		if ($laststate <> "unknown") {
	// 			updateRecord($dbtable,$charger,"state","unknown");
	// 			updateRecord($dbtable,$charger,"lastchange",$sql_datetime);
	// 		}

	// 		$lastchange = getRecords($dbtable,$charger,"lastchange");
	// 		$lastchange = $lastchange["lastchange"];
	// 		$lastchange = strtotime($lastchange);
	// 		$lastchange = date("YmdHis",$lastchange);

	// 	echo '
	// 		<item>
	// 		<title>'.$shortname.' could not be found.</title>
	// 		<link>http://www.evhighwaystatus.co.uk?upd='.$lastchange.'</link>
	// 		<description></description>
	// 		</item>';
	// }
}

$dataToEcho = $dataToEcho.
'
</channel>
</rss>';

echo $dataToEcho;

$agent = $_SERVER["HTTP_USER_AGENT"];

if (checkRssRecordExists($dbtable)) {
	rssUpdateAccessCount($dbtable,$sql_datetime,$agent);
} else {
	rssAccessRecord($dbtable,$sql_datetime,$agent);
	rssUpdateAccessCount($dbtable,$sql_datetime,$agent);
}

$conn->close();


$myFile = fopen("../user_inputs.txt","a");

$txt = stripslashes(file_get_contents("php://input")).chr(10).chr(10);
fwrite($myFile, $txt);
fclose($myFile);

?>