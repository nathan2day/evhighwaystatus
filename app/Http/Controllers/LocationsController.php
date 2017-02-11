<?php

namespace App\Http\Controllers;

use App\Repositories\ChargerRepository;
use DB;
use Illuminate\Http\Request;
use App\Charger;
use App\Provider;
use App\Connector;
use App\Transformers\ChargerTransformer;
use App\Transformers\HistoryTransformer;

class LocationsController extends Controller
{
    protected $chargerTransformer;
    protected $historyTransformer;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * @var ChargerRepository $chargerRepository
     */
    private $chargerRepository;

    /**
     * LocationsController constructor - inject dependencies.
     *
     * @param ChargerRepository $chargerRepository
     * @param ChargerTransformer $chargerTransformer
     * @param HistoryTransformer $historyTransformer
     * @param Request $request
     */
    public function __construct(ChargerRepository $chargerRepository,
                                ChargerTransformer $chargerTransformer,
                                HistoryTransformer $historyTransformer,
                                Request $request)
    {
        $this->chargerRepository = $chargerRepository;
        $this->chargerTransformer = $chargerTransformer;
        $this->historyTransformer = $historyTransformer;
        $this->request = $request;
    }

    public function getLocations()
    {
		$providers = $this->request->input('providers');
        $lowPower  = $this->request->input('lowpower') === 'true';

        $powerThreshold = $lowPower ? 0 : 21;

        // Skeleton of JSON response.
		$response = [
            'status'    => 'Success', // TODO
            'locations' => [],
            'providers' => $providers,
        ];

		// If no providers were defined, return empty response.
		if (!count($providers))
        {
            return response()->json($response);
        }
        
        $chargerIdsToReturn = $this->chargerRepository->chargerIdsAbovePower($powerThreshold);

        $response['locations'] = $this->chargerTransformer->transformCollection(
            Charger::whereIn('provider_id',$providers)
                ->find($chargerIdsToReturn)
                ->load('provider','connectors.type')
                ->all()
        );
            
        return response()->json($response);

    }

    public function history(Charger $charger)
    {
        return $this->historyTransformer->transformCollection(
            $charger->history()->with('trackable.type')->latest()->get()->all()
        );
    }
}
