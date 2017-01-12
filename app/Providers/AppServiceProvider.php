<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Connector;

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
		return new TwitterOAuth(
            env('TWITTER_CONSUMER_KEY'),
            env('TWITTER_CONSUMER_SECRET'),
            env('TWITTER_ACCESS_TOKEN'),
            env('TWITTER_ACCESS_TOKEN_SECRET'));
	});

	Connector::updated(function($connector){
		$att = (object) $connector->getAttributes();

		$charger = $connector->charger;

		$orig = (object) $connector->getOriginal();

        $connector->history()->create([
            'old' => $orig->status,
            'new' => $att->status,
        ]);

		echo "{$charger->provider->name} $charger->name ({$charger->id}) $connector->name ({$connector->position}) was {$orig->status} and is now {$att->status}<br>";

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
