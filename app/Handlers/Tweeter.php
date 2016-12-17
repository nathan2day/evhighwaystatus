<?php namespace App\Handlers;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\DB;
use App\Repositories\ChargerRepository;

class Tweeter
{
	protected $chargers;

	public function __construct(ChargerRepository $chargers)
	{
		$this->chargers = $chargers;
	}

	public function sendStatusTweet($provider)
	{
		$tweet = $this->chargers->statusTweet($provider);
		
		return $tweet;
	}

	


}
