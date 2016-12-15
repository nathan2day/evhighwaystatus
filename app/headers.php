<?php

// Our HSTS header
header('Strict-Transport-Security: max-age=31536000;');

$csp = [
	"default-src" => "self",
	"script-src"  => "self' https://*.google.com https://*.googleapis.com https://*.gstatic.com https://*.google-analytics.com https://use.fontawesome.com https://cdnjs.cloudflare.com https://connect.facebook.net https://cdn.eu.auth0.com/ 'unsafe-inline' 'unsafe-eval'",
	"style-src"   => "'self' https://*.googleapis.com https://*.gstatic.com https://cdnjs.cloudflare.com 'unsafe-inline' https://use.fontawesome.com",
	"font-src"    => "https://use.fontawesome.com https://*.googleapis.com https://*.gstatic.com",
	"frame-src"   => "https://www.google.com https://staticxx.facebook.com",
	"frame-ancestors" => "http://66.147.244.181/",
	"img-src"     => "'self' https://scontent.xx.fbcdn.net https://*.googleusercontent.com https://*.gstatic.com http://*.gstatic.com https://*.googleapis.com https://*.google-analytics.com https://www.facebook.com data:",
	"connect-src" => "'self' https://evhighwaystatus.eu.auth0.com",
];

$cspstring = '';

array_walk($csp, function($value, $key) use (&$cspstring) {
	$cspstring.= $key." ".$value.";";
});

header("Content-Security-Policy-Report-Only: $cspstring");
header('Public-Key-Pins: pin-sha256="W4eYBOqakMc20rqFQC9m+85m3tMCLkIAuPEgABvBYdE="; pin-sha256="YHpe2TH3OroEuHrq8rHvJpVmc8F9v+xze2Xy2WunlcI="; pin-sha256="40MfdrxxihJr3qpBky/kWTRHhJEQ7pLe8dhclIbOBEs="; max-age=60');

