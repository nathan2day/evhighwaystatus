<?php

// Our HSTS header
header('Strict-Transport-Security: max-age=2592000;');

header("Cache-Control: no-cache, must-revalidate, max-age=0 no-store");

$csp = [
	"default-src" => "'self'",
	"script-src"  => "'self' https://*.google.com https://*.googleapis.com https://*.gstatic.com https://*.google-analytics.com https://use.fontawesome.com https://cdnjs.cloudflare.com https://connect.facebook.net https://cdn.eu.auth0.com/ 'unsafe-inline' 'unsafe-eval'",
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

header("Content-Security-Policy: $cspstring");

