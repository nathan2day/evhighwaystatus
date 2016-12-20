<?php namespace App\Repositories;
use Illuminate\Support\Facades\DB;
class ChargerRepository
{
	protected $provider;

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

	public function getCount($provider,$type,$status)
    {
	    return DB::table('0_status')->where([
	            ['provider','=', $provider],
	            ['status'  ,'=', $status],
	            ['type'    ,'=', $type],
	            ['lastchecked','>',\Carbon\Carbon::now()->subHour(1)],
	    ])->count();

    }
}