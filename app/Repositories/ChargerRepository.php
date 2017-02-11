<?php namespace App\Repositories;
use App\Type;
use Illuminate\Support\Facades\DB;
use App\Provider;
use App\Connector;
use App\Charger;

class ChargerRepository
{

	public $provider;

    /**
     * @var Provider $providers
     */
	public $providers;

    /**
     * @var Charger $chargers
     */
	public $chargers;

    /**
     * @var Connector $connectors
     */
	public $connectors;

	public function __construct(Provider $providers,
                                Charger $chargers,
                                Connector $connectors,
                                Type $types)
	{

		$this->providers  = $providers;
		$this->chargers   = $chargers;
		$this->connectors = $connectors;
		$this->types = $types;
	}

    /**
     * Return an array of charger ids that have connectors above
     * the given threshold.
     *
     * @param $powerThreshold
     * @return mixed
     */
    public function chargerIdsAbovePower($powerThreshold)
    {
        return $this->connectors->where('power','>',$powerThreshold)
            ->distinct('charger_id')
            ->pluck('charger_id')
            ->all();
    }

    /**
     * Tidy-up for early stuff below here..
     */
    public function providers()
	{
		return 	$this->providers;
	}

	public function statusTweetFor($provider)
	{
		$this->provider = $provider;
		$date = \Carbon\Carbon::now()->format('j M');
		return "{$provider} on {$date}:\n".$this->typeOverview('CHAdeMO')."\n".$this->typeOverview('CCS')."\n".$this->typeOverview('AC (tethered)','AC')."\n#UKCharge";
	}

	private function typeOverview($type, $nameOverride = null)
	{
		$name = isset($nameOverride) ? $nameOverride : $type;
		return "$name: {$this->offline($type)} offline, {$this->online($type)} online ({$this->percentOnline($type)}%)";
	}

	private function percentOnline($type)
	{
		$percent = $this->online($type) / $this->total($type);
		$percent = round($percent * 100, 1, PHP_ROUND_HALF_UP);
		return number_format($percent);
	}

	private function online($type)
	{
		return $this->getCount($this->provider,$type,'online') + $this->getCount($this->provider,$type,'occupied');
	}

	private function offline($type)
    {
    	return $this->getCount($this->provider,$type,'offline');
    }

	private function total($type)
	{
		return $this->online($type) + $this->offline($type);
	}

	public function forProvider($provider)
	{
		return DB::table('0_status')->where([
			['provider','=', $provider],
                       	['lastchecked','>',\Carbon\Carbon::now()->subHour(1)],
            	])->get();
	}

	public function getCount($provider,$type,$status)
    {
	    return DB::connection('evhws')
                ->table('0_status')->where([
	            ['provider','=', $provider],
	            ['status'  ,'=', $status],
	            ['type'    ,'=', $type],
	            ['lastchecked','>',\Carbon\Carbon::now()->subHour(1)],
	    ])->count();

    }
}
