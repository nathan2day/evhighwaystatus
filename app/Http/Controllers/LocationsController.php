<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Charger;
use App\Transformers\ChargerTransformer;
use App\Repositories\ChargerRepository;

class LocationsController extends Controller
{
    public function __construct(ChargerRepository $chargers, ChargerTransformer $chargerTransformer)
    {
         $this->chargers = $chargers;
         $this->chargerTransformer = $chargerTransformer;
    }

    public function getLocations()
    {
		$providers = request()->input('providers');
		$data = [];

		if (count($providers) == 0){
			$data['locations'] = [];
		} else {
			$providerIds = \App\Provider::whereIn('name',$providers)->pluck('id');
		
			$data['locations'] = $this->chargerTransformer->transformCollection(
            			Charger::with(['provider','connectors'])
                			->whereIn('provider_id',$providerIds)
                			->get()->all()	
        		);
		}	
	   
       return $data;

    }
}
