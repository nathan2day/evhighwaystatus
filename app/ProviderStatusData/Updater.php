<?php namespace App\ProviderStatusData;

class Updater
{
	/**
	 * @var \App\ProviderStatusData\Fetcher
	 */
	private $fetcher;

	/**
	 * Our Fetcher class
	 *
	 * @param \App\ProviderStatusData\Fetcher $fetcher
	 */
	public function __construct(Fetcher $fetcher)
	{
		$this->fetcher = $fetcher;
	}

	public function run()
	{
//		$this->fetcher->get("https://secure.chargeyourcar.org.uk/map-api-iframe")
//			->parseWith(new Cyc);

		$this->fetcher->get("http://www.ecotricity.co.uk/for-the-road/our-electric-highway/")
			->parseWith(new Ecotricity);
	}

}