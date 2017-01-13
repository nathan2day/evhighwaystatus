<?php namespace App\ProviderStatusData;

use App\Repositories\ChargerRepository;
use App\Type;
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
		$this->locations = $this->parser->parse($this->response);
		$this->provider = $this->getProvider();
		$this->processLocations();
	}

	/**
	 * Process an array of provider locations.
	 *
	 */
	private function processLocations()
	{
		foreach ($this->locations as $location) {

			foreach ($location->connectors as $position => $sourceConnector) {

				$charger = $this->getCharger($location);
				//$existing = $charger->connectors()->where('name',$sourceConnector->type['title'])->count();
				//$position = $existing > 1 ? $position + 1 : $position; 
				// Get the connector for this charger
				$connector = $charger->connectors()
					->where('position',$position)
					->first();

				// If we have a result in $connector, we already have a record of this
				// particular connector and can therefore update the status. If
				// we don't, attach a new connector type to charger.
				if ($connector) {

					$connector->update(['status' => $sourceConnector->status]);

				} else {
					$charger->connectors()
						->create([
							'name'     => $sourceConnector->type['title'],
							'typeid'   => $sourceConnector->type['id'],
							'power'	   => $sourceConnector->power,
							'status'   => $sourceConnector->status,
							'position' => $position,
						])->type()->sync([$sourceConnector->type['id']]);
				}
			}
		}
	}

	/**
	 * Get the model associated with this set of locations.
	 *
	 * @return mixed
	 */
	private function getProvider() {
		return $this->chargers
			->providers
			->firstOrCreate([
				'name' => $this->locations[0]->provider,
				'url'  => $this->locations[0]->source['url'],
			]);
	}

	/**
	 * Get the model associated with a particular location.
	 *
	 * @param $location
	 * @return mixed
	 */
	private function getCharger($location)
	{
		return $this->provider
			->chargers()
			->firstOrCreate([
				'name' => $location->name,
				'lat'  => $location->lat,
				'lng'  => $location->lng,
			]);
	}
}
