<?php namespace App\ProviderStatusData;

use App\Repositories\ChargerRepository;
use \GuzzleHttp\Client;

class Fetcher
{
    protected $chargers;

    public function __construct(ChargerRepository $chargers)
    {
        $this->chargers = $chargers;
    }

    public function initHttpClient()
    {
        $this->http = new Client();
    }

    public function getData()
    {
        //$response = $this->http->request('GET',$this->uri);
	//$this->response = $response->getBody()->getContents();
	$this->response = goFetch($this->uri);
    }
}
