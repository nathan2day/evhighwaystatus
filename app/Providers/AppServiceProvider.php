<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Charger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
	$this->app->singleton('TweetSender',function(){
		return new TwitterOAuth(env('TWITTER_CONSUMER_KEY'),env('TWITTER_CONSUMER_SECRET'),env('TWITTER_ACCESS_TOKEN'),env('TWITTER_ACCESS_TOKEN_SECRET'));
	});

	Pivot::updated(function($pivot){
		$att = (object) $pivot->getAttributes();

		$charger = Charger::find($att->charger_id);

		$connector = $charger->connectors()
			->wherePivot('position',$att->position)
			->first();

		$orig = (object) $pivot->getOriginal();

		echo "{$charger->provider->name} $charger->name $connector->name was $orig->status and is now $att->status<br>";

	});

	//$this->app->singleton(\GuzzleHttp\Client::class, function(){
	//	var_dump('binding');
	//	return new \GuzzleHttp\Client(["base_uri"=>"https://secure.chargeyourcar.org.uk/map-api-iframe","timeout"=>5]);
	//});

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
