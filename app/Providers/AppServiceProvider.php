<?php

namespace App\Providers;

use GuzzleHttp\Client;
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

        $this->app->singleton(Client::class, function(){
            return new Client([
                'headers' => [
                    'User-Agent' => 'EVHighwayStatus.co.uk',
                ],
            ]);
        });

        Connector::updated(function($connector){
            // Determine the previous and new status 
            // for this connector
            $newAttr = (object) $connector->getAttributes();
            $origAttr = (object) $connector->getOriginal();
                    
            // Save a new history for this connector
            $connector->history()->create([
                'old' => $origAttr->status,
                'new' => $newAttr->status,
            ]);
        });

        Connector::created(function($connector){                   
            // Save a new history for this connector
            $connector->history()->create([               
                'new' => 'added',
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
