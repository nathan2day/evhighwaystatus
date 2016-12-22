<?php namespace App\ProviderStatusData;

use App\Repositories\ChargerRepository;
use App\ProviderStatusData\Interfaces\Parser;
use \GuzzleHttp\Client;

class Fetcher
{
	protected $chargers;
	protected $uri;

	/**
	 * Inject our charger repository and http client
	 *
	 * @param \App\Repositories\ChargerRepository $chargers
	 */
	public function __construct(ChargerRepository $chargers)
	{
		$this->chargers = $chargers;
		$this->http = new Client();
	}

	/**
	 * Set the URI to be used to fetch the provider data
	 *
	 * @param $uri   string  URI to fetch data from
	 * @return $this
	 */
	public function get($uri)
	{
		$this->uri = $uri;
		return $this;
	}

	/**
	 * Set the parser to be used on the response.
	 *
	 * @param \App\ProviderStatusData\Interfaces\Parser $parser
	 */
	public function parseWith(Parser $parser)
	{
		$this->parser = $parser;
		$this->getHttpResponse();
		$this->updateDatabase();
	}

	/**
	 * Get the response from the provider URI
	 *
	 */
	public function getHttpResponse()
	{
		$this->response = $this->http->request('GET',$this->uri)
			->getBody()
			->getContents();
	}

	public function updateDatabase()
	{
		$locations = $this->parser->parse($this->response);

		$provider = $this->chargers
			->providers
			->firstOrCreate([
				'name' => $locations[0]->provider,
				'url'  => $locations[0]->source['url'],
			]);

		foreach ($locations as $location){

			$charger = $provider->chargers()
				->firstOrCreate([
					'name' => $location->name,
					'lat'  => $location->lat,
					'lng'  => $location->lng,
				]);

			foreach($location->connectors as $position => $connector){
				
				$connectorType = $this->chargers->connectors
					->where('name',$connector->type['title'])
					->firstOrFail();

				$connectorInDatabase = $charger->connectors()
					//->wherePivot('position',$position)
					->where('name',$connectorType->name)
					->first();

				if ($connectorInDatabase) {
					// Update it
					$charger->connectors()
						->updateExistingPivot($connectorInDatabase->pivot->id, [
							'status'   => $connector->status
						]);
				} else {
					// Attach it
					$charger->connectors()
						->attach($connectorType,[
							'status'   => $connector->status,
							'position' => $position,
						]);
				}
			}
		}
	}
}
