<?php

if (!isset($_GET["provider"]) || $_GET["provider"] !== "ecotricity"){
  echo "Currently in Beta. Only GET param provider=ecotricity is supported for now.";
  die;
} 

$poiFile = new ov2file();
$poiFile->filename = "MyPOIs.ov2";

$chargers = json_decode(file_get_contents("json/".$_GET["provider"].".json"),true);

//var_dump($chargers);

foreach ($chargers["locations"] as $key => $charger) {
  $poiFile->add_POI($charger["lat"],$charger["lng"],$charger["name"]);
}


setHeaders($poiFile->filename);
echo $poiFile->content;

class ov2file
{
  // ov2file
  // (c) 2006 Sid Baldwin
  // Created on 06-Feb-2006
  var $content = "";
  var $filename = "default.ov2";
  function add_POI($lat,$long,$text) {
    $this -> content .= "\x02";
    $this -> content .= pack("I", 14 + strlen($text));
    $this -> content .= pack("i", round($long*100000));
    $this -> content .= pack("i", round($lat*100000));
    $this -> content .= $text;
    $this -> content .= "\x00";
    return;
  }
}

function setHeaders($filename){
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // some day in the past
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Content-type: application/x-download");
  header("Content-Disposition: attachment; filename={$filename}");
  header("Content-Transfer-Encoding: binary");
}

?>