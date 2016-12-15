<?php
header("Content-Type: text/plain");

require("database_r2.php");
require "autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;

$access_token = '4264974861-zyT00pzFSjnH02Tz7S1QA2lyOB8NwFdNSeYezMb';
$access_token_secret = 'lwjWJ7iHvPLGjaWlBH8qePVlj0HCeHjvysPl8J1zz8wHc';
$CONSUMER_KEY = 'uYMXRt5UDXdFWOJHKQ5uDMlBy';
$CONSUMER_SECRET = 'Ll423dC6weGJfq5zhrY32wDdQp8llNyRXkZe2vD0U3edVR779T';

$twitter = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $access_token, $access_token_secret);

$ccs_online = getChargerWeekSummary("CCS","online");
$ccs_offline = getChargerWeekSummary("CCS","offline");
$cha_online = getChargerWeekSummary("CHAdeMO","online");
$cha_offline = getChargerWeekSummary("CHAdeMO","offline");

$ccsOnlineText = pluralise("CCS",$ccs_online);
$ccsOfflineText = pluralise("CCS",$ccs_offline);
$chaOnlineText = pluralise("AC/DC",$cha_online);
$chaOfflineText = pluralise("AC/DC",$cha_offline);

If (($ccs_online > 0) || ($cha_online > 0)) {
	$theGood = true;
} else {
	$theGood = false;
}

If (($ccs_offline > 0) || ($cha_offline > 0)) {
	$theBad = true;
} else {
	$theBad = false;
}

if ($theGood){

	if (($cha_online > 0) && ($ccs_online > 0)) {
		$joiner = " and ";
	} else {
		$joiner = "";
	}
	
	$goodTweet = "";

	if ($cha_online > 0){
		$goodTweet .= $cha_online." ".$chaOnlineText;
	}

	$goodTweet .= $joiner;

	if ($ccs_online > 0){
		$goodTweet .= $ccs_online." ".$ccsOnlineText;
	}

	$goodTweet .= " came online.";
}

if ($theBad){
	if (($cha_offline > 0) && ($ccs_offline > 0)) {
		$joiner = " and ";
	} else {
		$joiner = "";
	}
	
	$badTweet = "";

	if ($cha_offline > 0){
		$badTweet .= $cha_offline." ".$chaOfflineText;
	}

	$badTweet .= $joiner;

	if ($ccs_offline > 0){
		$badTweet .= $ccs_offline." ".$ccsOfflineText;
	}

	$badTweet .= " went offline.";
}

if ($theGood || $theBad){
//We can tweet!
	$tweet = "Last week on the Electric Highway:".chr(10);

	if ($theGood && $theBad){
		$tweet .= $goodTweet.chr(10)
				 .$badTweet;
	} elseif ($theGood) {
		$tweet .= $goodTweet;
	} else {
		$tweet .= $badTweet;
	} 

	$tweet .= chr(10)."#UKCharge";

	$post = $twitter->post("statuses/update", ["status" => $tweet]);
}


$conn->close();

function pluralise($str,$charNum){
	if ($charNum > 1){
		return $str."s";
	} else {
		return $str;
	}
}


?>