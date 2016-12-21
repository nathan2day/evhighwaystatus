<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Abraham\TwitterOAuth\TwitterOAuth;

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
