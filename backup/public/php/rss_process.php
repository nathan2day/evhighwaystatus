<?php
require("database_r2.php");
header("Content-Type: application/json");
$v = json_decode(stripslashes(file_get_contents("php://input")),true);
$targetchargers = $v["targetChargers"];

if (count($targetchargers) >0 ){

	$bytes = openssl_random_pseudo_bytes(3);

	$f_uid = print_r(bin2hex($bytes),true);
	$filename=$f_uid.".php";

	$d_id = date('ymd');
	$dir = "../rss/".$d_id."/";

	if (!file_exists($dir) && !is_dir($dir)) {
	    mkdir($dir);         
	} 

	$fileloc="../rss/".date('ymd')."/".$filename;

	$myFile = fopen($fileloc,"w");

	if(strpos($_SERVER['HTTP_HOST'],'eta.')>0) {$db_prefix = "b";}

	$dbtable = $db_prefix.$d_id."_".$f_uid;

	dbAddTable($dbtable);

	$sql_datetime = date("Y-m-d H:i:s");


	for ($i=0; $i < count($targetchargers); $i++) { 
		insertChargerRecord($dbtable,$targetchargers[$i],$sql_datetime);
	}

	//start of php template
	$phptemplate='<?php'.chr(10).'require("../../php/rss_template_r2.php");?>';

	//end of template
		
	fwrite($myFile, $phptemplate);
	fclose($myFile);

	$dir = "../"."rss/".date('ymd')."/".$filename;

	if (file_exists($dir)) {
		
		echo "http://".$_SERVER['HTTP_HOST']."/"."rss/".date('ymd')."/".$filename;
	
	             
	} else {
		echo 'Sorry, something went wrong.';
	}
} 

else {
		echo 'Sorry, something went wrong.';
}

$conn->close();

?>