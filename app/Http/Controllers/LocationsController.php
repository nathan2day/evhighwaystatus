<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ChargerRepository;

class LocationsController extends Controller
{
    public function __construct(ChargerRepository $chargers)
    {
         $this->chargers = $chargers;
    }

    public function getLocations()
    {
		$providers = request()->input('providers');
		$data = [];
		$data['locations'] = \App\Charger::with(['provider','connectors'])->whereIn('provider_id',[2])->get()->toArray();		
	return $data;

    }
}
