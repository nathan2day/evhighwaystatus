<?php namespace App\ProviderStatusData\Interfaces;

interface Parser
{
	/**
	 * Take an HTTP response, and parse it to an array of
	 * of locations with connectors.
	 *
	 * @param $response
	 * @return array Locations with Connectors
	 */
	public function parse($response);
}