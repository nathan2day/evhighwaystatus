<?php



if(strpos($_SERVER['HTTP_HOST'],'eta.')>0 ) {
	echo '<script async src="js/script.orig.js"></script>
'; 
} else {

	$uniqueNum = date('His');

	if (filemtime('js/script.orig.js')>filemtime('js/script.min.js')) {
		$timestamp = date('His',filemtime('js/script.orig.js?'));
		$url = 'https://closure-compiler.appspot.com/compile';
		$srcUrl = 'https://evhighwaystatus.co.uk/js/script.orig.js?uid=' . $timestamp;
		
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

			if (strlen($result > 1000)){
				$myfile = fopen("js/script.min.js","w");
				fwrite($myfile, $result);
				fclose($myfile);
			}

		
			
			
		}

	} else {
		
	}

	$timestamp = date('His',filemtime('js/script.min.js?a='.$uniqueNum));
	echo '<script async src="js/script.min.js?uid=' . $timestamp . '"></script>
';
}
?>