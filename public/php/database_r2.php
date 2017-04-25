<?php 

$config = require(dirname(dirname(__DIR__)).'/config.php');

$conn = new mysqli($config['servername'], $config['username'], $config['password'],$config['dbname']);

if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
}

function dbAddTable($table){

	global $conn;

	$query = "CREATE TABLE ".$table." (id mediumint(9) PRIMARY KEY AUTO_INCREMENT, provider VARCHAR(50), name VARCHAR(150), lat DECIMAL(10,8), lng DECIMAL(10,8), type VARCHAR(20), status VARCHAR(10), lastchange DATETIME, lastchecked DATETIME)";

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

function checkChargerExists($table,$charger){
	global $conn;
	if (isset($charger["connectorIndex"])){
		$query = "SELECT * FROM ".$table." WHERE provider ='".$charger["provider"]."' AND lat ='".$charger["lat"]."' AND lng ='".$charger["lng"]."' AND type ='".$charger["type"]."' AND connectorindex = '".$charger["connectorIndex"]."' AND providerUniqueId = '".$charger["providerUniqueId"]."'";
	} else {
		$query = "SELECT * FROM ".$table." WHERE provider ='".$charger["provider"]."' AND lat ='".$charger["lat"]."' AND lng ='".$charger["lng"]."' AND type ='".$charger["type"]."'";
	}
	$result = $conn->query($query);

	If ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function insertChargerRecord($table,$charger,$lastchange){

	global $conn;

	$escapedname = $conn->real_escape_string($charger["name"]);

	if (isset($charger["connectorIndex"])){
		$query = "INSERT INTO ".$table." (provider, name, lat, lng, type, status, lastchange, lastchecked, connectorindex, providerUniqueId) VALUES ('".$charger["provider"]."',
																						   '".$escapedname."',
																						   '".$charger["lat"]."',
																						   '".$charger["lng"]."',
																						   '".$charger["type"]."',																						   																						   
																						   '".$charger["status"]."',
																						   '".$lastchange."',
																						   '".$lastchange."',
																						   '".$charger["connectorIndex"]."',
																						   '".$charger["providerUniqueId"]."')";

	} else {
		$query = "INSERT INTO ".$table." (provider, name, lat, lng, type, status, lastchange, lastchecked) VALUES ('".$charger["provider"]."',
																						   '".$escapedname."',
																						   '".$charger["lat"]."',
																						   '".$charger["lng"]."',
																						   '".$charger["type"]."',																						   																						   
																						   '".$charger["status"]."',
																						   '".$lastchange."',
																						   '".$lastchange."')";

	}

	
	$conn->query($query);
	echo $conn->error;
}

function insertChargerRecordHistory($table,$charger,$oldstatus,$newstatus,$lastchange){

	global $conn;
	$escapedname = $conn->real_escape_string($charger["name"]);

	if (isset($charger["connectorIndex"])){
		$query = "INSERT INTO ".$table." (provider, name, lat, lng, type, old_status, new_status, date_time, connectorindex, providerUniqueId) VALUES ('".$charger["provider"]."',
																						   '".$escapedname."',
																						   '".$charger["lat"]."',
																						   '".$charger["lng"]."',
																						   '".$charger["type"]."',
																						   '".$oldstatus."',
																						   '".$newstatus."',
																						   '".$lastchange."',
																						   '".$charger["connectorIndex"]."',
																						   '".$charger["providerUniqueId"]."')";

	} else {
		$query = "INSERT INTO ".$table." (provider, name, lat, lng, type, old_status, new_status, date_time) VALUES ('".$charger["provider"]."',
																						   '".$escapedname."',
																						   '".$charger["lat"]."',
																						   '".$charger["lng"]."',
																						   '".$charger["type"]."',
																						   '".$oldstatus."',
																						   '".$newstatus."',
																						   '".$lastchange."')";

	}
	
	$conn->query($query);
	echo $conn->error;
}

function checkRssRecordExists($table){
	global $conn;
	$query = "SELECT * FROM rss_access WHERE feed_url ='".$table."'";
	$result = $conn->query($query);

	If ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
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
function updateChargerStateOrLastUpdate($table,$charger,$col,$value){

	global $conn;

	$escapedvalue = $conn->real_escape_string($value);

	if (isset($charger["connectorIndex"])){
		$query = "UPDATE ".$table." SET ".$col."='".$escapedvalue."' WHERE provider ='".$charger["provider"]."' AND lat ='".$charger["lat"]."' AND lng ='".$charger["lng"]."' AND type ='".$charger["type"]."' AND connectorindex ='".$charger["connectorIndex"]."' AND providerUniqueId = '".$charger["providerUniqueId"]."'";	
	} else {
		$query = "UPDATE ".$table." SET ".$col."='".$escapedvalue."' WHERE provider ='".$charger["provider"]."' AND lat ='".$charger["lat"]."' AND lng ='".$charger["lng"]."' AND type ='".$charger["type"]."'";
	}

	$conn->query($query);
	echo $conn->error;
}

function getChargerStateOrLastUpdate($table,$charger,$col){

	global $conn;

	if (isset($charger["connectorIndex"])){
		$query = "SELECT ".$col." FROM ".$table." WHERE provider ='".$charger["provider"]."' AND lat ='".$charger["lat"]."' AND lng ='".$charger["lng"]."' AND type ='".$charger["type"]."' AND connectorindex ='".$charger["connectorIndex"]."' AND providerUniqueId = '".$charger["providerUniqueId"]."'";
	} else {
		$query = "SELECT ".$col." FROM ".$table." WHERE provider ='".$charger["provider"]."' AND lat ='".$charger["lat"]."' AND lng ='".$charger["lng"]."' AND type ='".$charger["type"]."'";
	}

	$result = $conn->query($query);

	if ($result->num_rows > 0) {
		return $result->fetch_assoc();
	} else {
		return false;
	}
	echo $conn->error;
}

function getChargerRecords($table,$provider){

	global $conn;

	if (isset($provider) && $provider <> "all"){
		$query = "SELECT * FROM ".$table." WHERE provider ='".$provider;
	} else {
		$query = "SELECT * FROM ".$table;
	}


	$result = $conn->query($query);

	return $result->fetch_all(MYSQLI_ASSOC);
}

function getChargerHistory($charger){

	global $conn;

	if ($charger["provider"] == "Ecotricity"){
		$table = "0_ecotricity";
	} else {
		$table = "0_history";
	}

	$query = "SELECT * FROM ".$table." WHERE provider = '".$charger["provider"]."' AND lat ='".$charger["lat"]."' AND lng ='".$charger["lng"]."' ORDER BY date_time DESC LIMIT " .$charger["limit"];

	$result = $conn->query($query);

	if ($result->num_rows > 0) {
		return $result->fetch_all(MYSQLI_ASSOC);
	} else {
		return false;
	}
	
}

function getChargerStatusSummary($type,$status,$provider){

	global $conn;

	$sql_datetime = date("Y-m-d");

	$query = "SELECT * FROM 0_status WHERE provider = '".$provider."' AND type = '".$type."' AND status = '".$status."' AND lastchecked LIKE '".$sql_datetime."%'";

	$result = $conn->query($query);

	if ($result !== false) {
		return $result->num_rows;
	} else {
		return false;
	}
	
}

function updateOCMCharger($charger){
	global $conn;
	$sql_datetime = date("Y-m-d H:i:s");

	//check to see if we've got it

	$query = "SELECT * FROM 0_OCM_data WHERE UUID = '".$charger["UUID"]."'";
	$result = $conn->query($query);

	if ($result->num_rows > 0) {
		//we have this charger
		$query = "UPDATE 0_OCM_data SET json_data = '".$conn->real_escape_string(json_encode($charger))."', last_updated = '".$sql_datetime."', lat = '".$charger["AddressInfo"]["Latitude"]."', lng = '".$charger["AddressInfo"]["Longitude"]."', operator_id = '".$charger["OperatorID"]."' WHERE UUID = '".$charger["UUID"]."'";

		if ($conn->query($query) === true)
			return "updated";
		else {
			return "update error - ".$conn->error;
		}
		
		

	} else {
		//we dont' have this charger
		$query = "INSERT INTO 0_OCM_data (UUID, last_updated, json_data, lat, lng, operator_id) VALUES ('".$charger["UUID"]."','".$sql_datetime."','".$conn->real_escape_string(json_encode($charger))."', '".$charger["AddressInfo"]["Latitude"]."', '".$charger["AddressInfo"]["Longitude"]."', '".$charger["OperatorID"]."')";
		
		if ($conn->query($query) === true)
			return "added";
		else {
			return "add error - ".$conn->error;
		}
	} 

	return "failed";

}

function getOCMChargers($params = NULL){

	global $conn;

	$sql_datetime = date("Y-m-d H:i:s");
	$sql_datetime_lt = date_create_from_format("Y-m-d H:i:s", $sql_datetime);
	date_add($sql_datetime_lt,date_interval_create_from_date_string('-1 day'));

	if (isset($params["operator_id"])){
		$query = "SELECT * FROM 0_OCM_data WHERE operator_id = '".$params["operator_id"]."' AND last_updated > '".date_format($sql_datetime_lt,"Y-m-d H:i:s")."'";
	} else {
		$query = "SELECT * FROM 0_OCM_data WHERE last_updated > '".date_format($sql_datetime_lt,"Y-m-d H:i:s")."'";
	}

	
	$result = $conn->query($query);

	return $result->fetch_all(MYSQLI_ASSOC);
	
	
}

function getChargerWeekSummary($type,$status){

	global $conn;

	$sql_datetime = date("Y-m-d");
	$sql_datetime_lt = date_create_from_format('Y-m-d', $sql_datetime);
	$sql_datetime_ut = date_create_from_format('Y-m-d', $sql_datetime);
	date_add($sql_datetime_lt,date_interval_create_from_date_string('-8 days'));
	date_add($sql_datetime_ut,date_interval_create_from_date_string('-1 days'));


	$query = "SELECT DISTINCT name, type, lat, lng FROM 0_status WHERE provider = 'Ecotricity' AND type = '".$type."' AND status = '".$status."' AND lastchange > '".date_format($sql_datetime_lt,"Y-m-d")."' AND lastchange < '".date_format($sql_datetime_ut,"Y-m-d")."'";

	$result = $conn->query($query);

	if ($result->num_rows > 0) {
		return $result->num_rows;
	} else {
		return false;
	}
	
}

function getChargerRecordsSearch($table,$term){

	global $conn;

	$escapedterm = $conn->real_escape_string($term);
	$escapedterm = addcslashes($escapedterm, "%_");
	
	$query = "SELECT * FROM ".$table." WHERE name LIKE '%".$escapedterm."%'";
	
	$result = $conn->query($query);

	return $result->fetch_all(MYSQLI_ASSOC);
}

function checkEmailExists($params){
	global $conn;
	

	if ($params["ios"] === 'true') {
		$table = "0_testflight_users";
	} else {
		$table = "0_beta_users";
	}

	$stmnt = $conn->prepare("SELECT * FROM ".$table." WHERE Email_Address = ?");
	$stmnt->bind_param("s",$theaddress);

	$theaddress = $conn->real_escape_string($params["email"]);

	//$query = "SELECT * FROM ".$table." WHERE Email_Address ='".$theaddress."'";
	
	//$result = $conn->query($query);

	$result = $stmnt->execute();
	$result = $stmnt->get_result();
	$stmnt->close();

	If ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}
function checkEmailValidatedState($params){
	global $conn;
	
	if ($params["ios"] === 'true') {
		$table = "0_testflight_users";
	} else {
		$table = "0_beta_users";
	}

	$stmnt = $conn->prepare("SELECT Email_Validated FROM ".$table." WHERE User_ID = ?");
	$stmnt->bind_param("s",$user_id);

	$user_id = $conn->real_escape_string($params["user_id"]);
	
	$result = $stmnt->execute();
	$result = $stmnt->get_result();
	$stmnt->close();

	If ($result->num_rows > 0) {
		$result = $result->fetch_all(MYSQLI_ASSOC)[0]["Email_Validated"];
		return $result;
	} else {
		return false;
	}
}

function getActivatedBetaUsers($params = NULL){
	global $conn;

	if (is_array($params) && isset($params["test"])){
		$stmnt = $conn->prepare("SELECT * FROM 0_beta_users_test WHERE Email_Validated = ? ORDER BY User_Index DESC");
	} else {
		$stmnt = $conn->prepare("SELECT * FROM 0_beta_users WHERE Email_Validated = ? ORDER BY User_Index DESC");
	}

	$stmnt->bind_param("s",$validated);

	$validated = "yes";
	
	$result = $stmnt->execute();
	$result = $stmnt->get_result();
	$stmnt->close();

	$result = $result->fetch_all(MYSQLI_ASSOC);
	return $result;
}

function updateEmailValidatedState($params){
	global $conn;

	if ($params["ios"] === 'true') {
		$table = "0_testflight_users";
	} else {
		$table = "0_beta_users";
	}

	$stmnt = $conn->prepare("UPDATE ".$table." SET Email_Validated = 'yes'  WHERE User_ID = ?");
	$stmnt->bind_param("s",$user_id);

	$user_id = $conn->real_escape_string($params["user_id"]);
	
	$result = $stmnt->execute();
	$stmnt->close();

	return $result;
}

function getSpecificBetaUserData($params){
	global $conn;

	$stmnt = $conn->prepare("SELECT * FROM 0_beta_users WHERE User_ID = ?");
	$stmnt->bind_param("s",$user_id);

	$user_id = $conn->real_escape_string($params["user_id"]);
	
	$result = $stmnt->execute();
	$result = $stmnt->get_result();
	$stmnt->close();

	
	$result = $result->fetch_all(MYSQLI_ASSOC);
	return $result;
	
}

function addBetaUser($params){

	global $conn;

	if ($params["ios"] === 'true') {
		$table = "0_testflight_users";
	} else {
		$table = "0_beta_users";
	}

	$stmnt = $conn->prepare("INSERT INTO ".$table." (User_ID, First_Name, Last_Name, Email_Address, Date_Created, Email_Validated) VALUES (?,?,?,?,?,?)");
	$stmnt->bind_param("ssssss",$uid, $first, $last, $email, $sql_datetime, $validated);
	
	$validated = "no";
	$sql_datetime = date("Y-m-d H:i:s");
	$uid = $params["userid"];
	$first = $conn->real_escape_string($params["firstName"]);
	$last = $conn->real_escape_string($params["lastName"]);
	$email = $conn->real_escape_string($params["email"]);

	$result = $stmnt->execute();
	$stmnt->close();
	
	return $result;
}


?>
