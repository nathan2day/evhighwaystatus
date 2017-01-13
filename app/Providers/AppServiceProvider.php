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

		$orig = (object) $connector->getOriginal();

		$connector->history()->create([
		    'old' => $orig->status,
		    'new' => $att->status,
		]);
	});
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
