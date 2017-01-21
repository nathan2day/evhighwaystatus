<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Charger;
use App\Provider;
use App\Connector;
use App\Transformers\ChargerTransformer;
use App\Transformers\HistoryTransformer;

class LocationsController extends Controller
{
    protected $chargerTransformer;

    public function __construct(ChargerTransformer $chargerTransformer, HistoryTransformer $historyTransformer)
    {
         $this->chargerTransformer = $chargerTransformer;
         $this->historyTransformer = $historyTransformer;
    }

    public function getLocations()
    {
		$providers = request()->input('providers');
        $lowPower  = (bool) request()->input('lowpower');

		$data = [
            'status'    => 'Success', // TODO
            'locations' => [],
        ];

		if (count($providers)) {

			$providerIds =  Provider::whereIn('name',$providers)->pluck('id');
            $timestart = \Carbon\Carbon::now();
            
            if ($lowPower)
            {
                $chargerIdsToReturn = \DB::table('connectors')
                                    ->where('power','>',0)
                                    ->distinct('charger_id')
                                    ->pluck('charger_id')
                                    ->all();               
            }
            
            if (!$lowPower)
            {
                $chargerIdsToReturn = \DB::table('connectors')
                                    ->where('power','>',21)
                                    ->distinct('charger_id')
                                    ->pluck('charger_id')
                                    ->all();
            }
            
            $chargerCollection =  Charger::whereIn('provider_id',$providerIds)
                                            ->find($chargerIdsToReturn )
                                            ->load('provider','connectors.type')
                                            ->all();

            $collectionTime = \Carbon\Carbon::now()->diffInSeconds($timestart);
            $collectEnd = \Carbon\Carbon::now();
            $data['locations'] = $this->chargerTransformer->transformCollection($chargerCollection);
            
		}	
	   $data['stats'] = ['collect'=>$collectionTime, 'load'=>\Carbon\Carbon::now()->diffInSeconds($collectEnd)];
       return $data;

    }

    public function history(Charger $charger)
    {
        return $this->historyTransformer->transformCollection(
            $charger->history()->with('trackable.type')->get()->all()
        );
    }
}
