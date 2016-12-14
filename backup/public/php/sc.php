<?php



$url = 'http://closure-compiler.appspot.com/compile';
$timestamp = date('His');
if ($_SESSION["beta"]){
	$srcUrl = "https://{$_SERVER['HTTP_HOST']}/js/script.beta.orig.js?uid=".$timestamp;
} else {
	$srcUrl = "https://{$_SERVER['HTTP_HOST']}/js/script.orig.js?uid=".$timestamp;
}

$data = array('code_url' => $srcUrl,
			  'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
			  'language' => 'ECMASCRIPT5',
			  'output_format' => 'text',
			  'output_info' => 'compiled_code',
);	

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) { 
	/* Handle error */ 
	
} else {
	
	if (strlen($result) > 1000){
		if ($_SESSION["beta"]){
			$myfile = fopen("js/script.beta.min.js","w");
		} else {
			$myfile = fopen("js/script.min.js","w");
		}
		
		fwrite($myfile, $result);
		fclose($myfile);
	}
}

?>
