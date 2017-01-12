<?php

namespace App\Transformers;

use App\Transformers\ConnectorTransformer;

class ChargerTransformer extends Transformer
{
	protected $connectorTransformer;

	public function __construct(ConnectorTransformer $connectorTransformer)
	{
		$this->connectorTransformer = $connectorTransformer;
	}

	public function transform($charger)
	{
		return [
			'provider'		=> $charger->provider->name,
			'name'			=> $charger->name,
			'lat'			=> $charger->lat,
			'lng'			=> $charger->lng,
			'postcode'		=> $charger->postcode,
			'source'		=> [
				'name'		=> $charger->provider->name,
				'url'		=> $charger->provider->url,
			],
			'connectors'	=> $this->connectorTransformer->transformCollection($charger->connectors);
		];
	}
}