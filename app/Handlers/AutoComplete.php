<?php

namespace App\Handlers;

use GuzzleHttp\Client;

class AutoComplete
{
    private $http;

    public function __construct()
    {
        $this->apiKey = env('PLACES_API_KEY');
        $this->http = new Client([
            'headers' => [
                'User-Agent' => 'EVHighwayStatus.co.uk',
            ],
        ]);
    }

    public function query($query,$location,$radius)
    {
        return $this->http->request(
            'GET',
            $this->getQueryUrl($query,$location,$radius)
        )->getBody()->getContents();
    }

    /**
     * Get the query URL for the Google Places API.
     *
     * @param $query
     * @param $location
     * @param $radius
     * @return string
     */
    private function getQueryUrl($query, $location, $radius)
    {
        $safeQuery = urlencode($query);

        return "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$safeQuery&location=$location&radius=$radius&key=$this->apiKey";
    }
}
