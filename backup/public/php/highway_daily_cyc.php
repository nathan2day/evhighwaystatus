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

$date = date("d M");

$ccs_online = getChargerStatusSummary("CCS","online","CYC") + getChargerStatusSummary("CCS","in session","CYC");
$ccs_offline = getChargerStatusSummary("CCS","offline","CYC");
$cha_online = getChargerStatusSummary("CHAdeMO","online","CYC") + getChargerStatusSummary("CHAdeMO","in session","CYC");
$cha_offline = getChargerStatusSummary("CHAdeMO","offline","CYC");
$ac_online = getChargerStatusSummary("AC (tethered)","online","CYC") + getChargerStatusSummary("AC (tethered)","in session","CYC");
$ac_offline = getChargerStatusSummary("AC (tethered)","offline","CYC");

$cha_perc = round(($cha_offline/($cha_online+$cha_offline))*100,1,PHP_ROUND_HALF_UP);
$ccs_perc = round(($ccs_offline/($ccs_online+$ccs_offline))*100,1,PHP_ROUND_HALF_UP);
$ac_perc = round(($ac_offline/($ac_online+$ac_offline))*100,1,PHP_ROUND_HALF_UP);

$cha_perc = number_format(100 - $cha_perc,1);
$ccs_perc = number_format(100 - $ccs_perc,1);
$ac_perc = number_format(100 - $ac_perc,1);

$tweet = "CYC on ".$date.":".chr(10).
		 "CHAdeMO: ".$cha_offline." offline, ".$cha_online." online (".$cha_perc."%)".chr(10).
		 "CCS: ".$ccs_offline." offline, ".$ccs_online." online (".$ccs_perc."%)".chr(10).
		 "AC: ".$ac_offline." offline, ".$ac_online." online (".$ac_perc."%)".chr(10)."#UKCharge";

echo $tweet;

$post = $twitter->post("statuses/update", ["status" => $tweet]);

$conn->close();


?>