<?php 

$servername = "10.169.0.62";
$username = "evhighwa_data";
$password = "thisistheevhighwaystatus";
$dbname = "evhighwa_data";

$conn = new mysqli($servername, $username, $password,$dbname);

if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
}


function dbAddTable($table){

	global $conn;

	$query = "CREATE TABLE ".$table." (id mediumint(9) PRIMARY KEY AUTO_INCREMENT,	name VARCHAR(200), state VARCHAR(20), lastchange DATETIME)";

	$conn->query($query);
}

function checkTableExists($table){
	global $conn;
	$query = "SHOW TABLES LIKE '".$table."'";
	$result = $conn->query($query);

	If ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}

}

function checkRowExists($table,$col,$entry){
	global $conn;
	$query = "SELECT * FROM ".$table." WHERE ".$col."='".$entry."'" ;
	$result = $conn->query($query);

	If ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function insertChargerRecord($table,$name,$state,$lastchange){

	global $conn;

	$escapename = $conn->real_escape_string($name);

	$query = "INSERT INTO ".$table." (name, state, lastchange) VALUES ('".$escapename."', '".$state."', '".$lastchange."')";

	$conn->query($query);
}

function rssAccessRecord($table_accessed,$access_datetime,$agent){

	global $conn;

	$query = "INSERT INTO rss_access (feed_url, date_time) VALUES ('".$table_accessed."', '".$access_datetime."')";
	$conn->query($query);

	$encodedagent = $conn->real_escape_string($agent);

	$query = "UPDATE rss_access SET lastagent='".$encodedagent."' WHERE feed_url='".$table_accessed."'";
	$conn->query($query);
}

function rssUpdateAccessCount($feed_url,$access_time,$agent){
	global $conn;

	$query = "UPDATE rss_access SET count=count+1 WHERE feed_url='".$feed_url."'";
	$conn->query($query);

	$query = "UPDATE rss_access SET date_time='".$access_time."' WHERE feed_url='".$feed_url."'";
	$conn->query($query);

	$encodedagent = $conn->real_escape_string($agent);

	$query = "UPDATE rss_access SET lastagent='".$encodedagent."' WHERE feed_url='".$feed_url."'";
	$conn->query($query);

}
function updateRecord($table,$name,$col,$value){

	global $conn;

	$escapename = $conn->real_escape_string($name);

	$query = "UPDATE ".$table." SET ".$col."='".$value."' WHERE name='".$escapename."'";

	$conn->query($query);
}

function getRecords($table,$name,$col){

	global $conn;

	$escapename = $conn->real_escape_string($name);

	$query = "SELECT ".$col." FROM ".$table." WHERE name='".$escapename."'";

	$result = $conn->query($query);

	return $result->fetch_assoc();
}

?>