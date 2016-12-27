<?php namespace App\Handlers;

use Illuminate\Support\Facades\DB;
use App\Repositories\ChargerRepository;
use Illuminate\Notifications\Notifiable;
use App\Notifications\StatusTweetSent;
use App\Notifications\StatusTweetError;

class Tweeter
{
    use Notifiable;

	protected $chargers;

	public function routeNotificationForSlack()
    {
	    return env('ADMIN_SLACK_URL');
    }

	public function __construct()
	{
		$this->tweeter = app('TweetSender');
	}

	public function testNotification()
	{
		$this->notify(new StatusTweetSent('A successfull tweet'));
		$this->notify(new StatusTweetError('A failed tweet'));
	}
	public function post($status)
	{
		$this->tweeter->post("statuses/update",[
			"status" => $status,
		]);

		if ($this->tweeter->getLastHttpCode() == 200) {
			$this->notify(new StatusTweetSent($status));
			return true;
		} else {
			$this->notify(new StatusTweetError($status));
				return false;
		}
	}
}
