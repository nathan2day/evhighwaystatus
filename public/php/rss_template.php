<?php
header("Content-type: text/xml");
include("../../php/database.php");
$xmlstatus = new DOMDocument();
$xmlstring = file_get_contents("../../xml/status.xml");
$xmlstatus->loadXML($xmlstring);
$names = $xmlstatus->getElementsByTagName("Name");
$types = $xmlstatus->getElementsByTagName("Type");
$offlines = $xmlstatus->getElementsByTagName("Offline");
$lats = $xmlstatus->getElementsByTagName("Lat");
$lngs = $xmlstatus->getElementsByTagName("Lng");

echo

'<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
  <title>Electric Highway Update</title>
  <link>http://www.evhighwaystatus.co.uk</link>
  <description>Your update on the Electric Highway</description>
  <language>en</language>
  <image>
  	  <title>Electric Highway Update</title>
      <url>http://www.evhighwaystatus.co.uk/img/apple-icon-152x152.png</url>
      <link>http://www.evhighwaystatus.co.uk</link>
  </image>';

$file_name = substr($_SERVER['SCRIPT_FILENAME'],strrpos($_SERVER['SCRIPT_FILENAME'],"/")+1,6);
$dir_name = substr($_SERVER['SCRIPT_FILENAME'],0,strrpos($_SERVER['SCRIPT_FILENAME'],"/"));
$dir_name = substr($dir_name,strrpos($dir_name,"/")+1,6);

$dbtable = $dir_name."_".$file_name;

$sql_datetime = date("Y-m-d H:i:s");

if (strpos($_SERVER['SCRIPT_FILENAME'],"eta.") > 0) {$dbtable = "b".$dbtable;}

if (checkTableExists($dbtable)) {
	//table already there
} else {
	dbAddTable($dbtable); 
	for ($i=0; $i < count($targetchargers); $i++) { 
 		insertChargerRecord($dbtable,$targetchargers[$i],'new',$sql_datetime);
 	}
}






foreach ($targetchargers as $charger) {
	$state_changed = false;
	$charger_found = false;

	for ($i=0; $i < $names->length; $i++) {

		if (($charger) == ($names->item($i)->nodeValue . $types->item($i)->nodeValue)) {
			$charger_found = true;
			$laststate = getRecords($dbtable,$charger,"state");
			$laststate = $laststate["state"];

			if (($offlines->item($i)->nodeValue)==1){
				$state = "off line";

				if ($laststate <> "offline") {
					updateRecord($dbtable,$charger,"state","offline");
					updateRecord($dbtable,$charger,"lastchange",$sql_datetime);
				}

			} else {
				$state = "on line";

				if ($laststate <> "online") {
					updateRecord($dbtable,$charger,"state","online");
					updateRecord($dbtable,$charger,"lastchange",$sql_datetime);
				}
			}
			
			if (strrpos($names->item($i)->nodeValue,",") > 0) {
				$shortname = explode(",",$names->item($i)->nodeValue);
				$shortname = $shortname[0].", ".$shortname[1];
			} else {
				$shortname = $names->item($i)->nodeValue;
			}

			$lastchange = getRecords($dbtable,$charger,"lastchange");
			$lastchange = $lastchange["lastchange"];
			$lastchange = strtotime($lastchange);
			$lastchange = date("YmdHis",$lastchange);

			echo '
			<item>
			<title>'.$shortname.' is currently '.$state.'</title>
			<link>http://www.evhighwaystatus.co.uk?lat='.$lats->item($i)->nodeValue.'&amp;lng='.$lngs->item($i)->nodeValue.'&amp;upd='.$lastchange.'</link>
			<description></description>
			</item>';
		}
	}

	if (!$charger_found) {

			$laststate = getRecords($dbtable,$charger,"state");
			$laststate = $laststate["state"];

			if ($laststate <> "unknown") {
				updateRecord($dbtable,$charger,"state","unknown");
				updateRecord($dbtable,$charger,"lastchange",$sql_datetime);
			}

			$lastchange = getRecords($dbtable,$charger,"lastchange");
			$lastchange = $lastchange["lastchange"];
			$lastchange = strtotime($lastchange);
			$lastchange = date("YmdHis",$lastchange);

		echo '
			<item>
			<title>'.$shortname.' could not be found.</title>
			<link>http://www.evhighwaystatus.co.uk?upd='.$lastchange.'</link>
			<description></description>
			</item>';
	}


}

echo
'
</channel>
</rss>';

$agent = $_SERVER["HTTP_USER_AGENT"];


if (checkRowExists("rss_access","feed_url",$dbtable)) {
	rssUpdateAccessCount($dbtable,$sql_datetime,$agent);
} else {
	rssAccessRecord($dbtable,$sql_datetime,$agent);
	rssUpdateAccessCount($dbtable,$sql_datetime,$agent);
}

$conn->close();

?>