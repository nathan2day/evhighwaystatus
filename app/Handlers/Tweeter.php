<?php namespace App\Handlers;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\DB;
use App\Repositories\ChargerRepository;

class Tweeter
{
	protected $chargers;

	public function __construct()
	{
		$this->tweeter = app('TweetSender');
	}

	public function post($status)
	{
		$this->tweeter->post("statuses/update",[
			"status" => $status,
		]);

		if ($this->tweeter->getLastHttpCode() == 200) {
    			return true;
		} else {
   			return false;
		}
	}
}
