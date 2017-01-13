<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Charger;
use App\Provider;
use App\Transformers\ChargerTransformer;

class LocationsController extends Controller
{
    protected $chargerTransformer;

    public function __construct(ChargerTransformer $chargerTransformer, HistoryTransformer $HistoryTransformer)
    {
         $this->chargerTransformer = $chargerTransformer;
         $this->historyTransformer = $historyTransformer
    }

    public function getLocations()
    {
		$providers = request()->input('providers');

		$data = [
            'status'    => 'Success', // TODO
            'locations' => [],
        ];

		if (count($providers) > 0){

			$providerIds = Provider::whereIn('name',$providers)->pluck('id');
		
			$data['locations'] = $this->chargerTransformer->transformCollection(
            			Charger::with(['provider','connectors'])
                			->whereIn('provider_id',$providerIds)
                			->get()->all()	
        	);
		}	
	   
       return $data;

    }

    public function history(Charger $charger)
    {
        return $this->HistoryTransformer->transformCollection(
            $charger->history,
        );
    }
}
