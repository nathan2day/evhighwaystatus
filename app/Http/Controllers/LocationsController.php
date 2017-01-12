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
		$data['locations'] = $this->chargers->providers->with('connectors')->whereIn($providers)->get()->toArray();

	return $data;

    }
}
