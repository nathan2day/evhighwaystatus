<?php
header("Content-type:text/plain");

if(strpos($_SERVER['HTTP_HOST'],'eta.')>0) {
	echo file_get_contents('../js/script.js');
} else {

	$url = 'http://closure-compiler.appspot.com/compile';
	$data = array('code_url' => 'http://evhighwaystatus.co.uk/js/script.js',
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

	if ($result === FALSE) { /* Handle error */ }

	echo($result);
}
?>