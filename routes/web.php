<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'HomeController@index');



Route::get('test', function(App\ProviderStatusData\Updater $updater){
    $updater->run();
});

Route::post('heatmyleaf', function(\App\Utilities\HeatMyLeaf $heatMyLeaf){
    $response = $heatMyLeaf->with(
        request()->input('username'),
        request()->input('password')
    )->perform('battery');

    return response()->json($response);
});
