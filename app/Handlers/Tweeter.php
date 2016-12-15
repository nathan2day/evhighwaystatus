<?php namespace App\Handlers;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\DB;

class Tweeter
{
	public function tweetStatus($provider)
	{
		$this->provider = $provider;

		return $this->typeOverview('CCS')."\n".$this->typeOverview('CHAdeMO')."\n".$this->typeOverview('AC (tethered)');
	}

	private function typeOverview($type)
	{
		return "$type: {$this->offline($type)} offline, {$this->online($type)} online ({$this->percentOnline($type)}%).";
	}

	private function percentOnline($type)
	{
		$percent = $this->online($type) / $this->total($type);
		$percent = round($percent * 100, 1, PHP_ROUND_HALF_UP);
		return number_format($percent);
	}

	private function online($type)
	{
		return $this->getCount($this->provider,$type,'online') + $this->getCount($this->provider,$type,'in session');
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
