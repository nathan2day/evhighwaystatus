<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationsController extends Controller
{
    public function getLocations()
    {
	$providers = request()->input('providers');
	$data = [];
	$data['locations'] = [];
	foreach ($providers as $provider) {
	$file = '/home/forge/evhighwaystatus.co.uk/public/json/'.$provider.'.json';
		if (!is_file($file)) {
			continue;
		}
		$locations = json_decode(file_get_contents($file),true)['locations'];
		foreach ($locations as $location) {
			$data['locations'][] = $location;
		}
	}
	return $data;
    }
}
