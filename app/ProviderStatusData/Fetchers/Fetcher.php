<?php namespace App\ProviderStatusData\Fetchers;

use App\Repositories\ChargerRepository;
use App\ProviderStatusData\Interfaces\Parser;
use \GuzzleHttp\Client;

abstract class Fetcher
{
    protected $chargers;

    /**
     * The parse with which to interpret the response from the URI.
     *
     * @var Parser $parser
     */
    protected $parser;

    /**
     * The URI to fetch the data from.
     *
     * @var string $uri
     */
    protected $uri;

    protected $response;
    protected $locations;
    protected $provider;

    /**
     * Inject our charger repository and http client
     */
    public function __construct()
    {
        $this->chargers = app(ChargerRepository::class);
        $this->http = new Client;
    }

    /**
     * Ensure a setParser function is on the instance.
     */
    abstract protected function setParser();

    /**
     * Run the data fetcher.
     */
    public function run()
    {
        $this->setUri();
        $this->setParser();
        $this->getHttpResponse();
        $this->updateDatabase();
    }

    protected function setUri()
    {
        if (method_exists($this,'uri'))
        {
            $this->uri = $this->uri();
        }
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
        $this->processLocations();
    }

    /**
     * Process an array of provider locations.
     *
     */
    private function processLocations()
    {
        if (!count($this->locations)) return;

        $this->provider = $this->getProvider();

        foreach ($this->locations as $location) {

            foreach ($location->connectors as $position => $sourceConnector) {

                $charger = $this->getCharger($location);

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
                    $connector = $charger->connectors()
                        ->create([
                            'power'	   => $sourceConnector->power,
                            'status'   => $sourceConnector->status,
                            'position' => $position,
                            'quantity'    => isset($sourceConnector->quantity) ? $sourceConnector->quantity : 1,
                        ]);

                    $connector->type()->sync([
                        $sourceConnector->type['id']
                    ]);
                }
            }
        }
    }

    /**
     * Get the model associated with this set of locations.
     *
     * @return \App\Provider
     */
    private function getProvider()
    {
        return $this->chargers
            ->providers
            ->firstOrCreate([
                'name' => $this->locations[array_keys($this->locations)[0]]->provider,
                'url' => $this->locations[array_keys($this->locations)[0]]->source['url'],
                'ocm_id' => $this->locations[array_keys($this->locations)[0]]->provider_openid,
            ]);
    }

    /**
     * Get the model associated with a particular location.
     *
     * @param $location
     * @return \App\Charger $charger
     *
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
