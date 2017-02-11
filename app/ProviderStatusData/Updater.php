<?php namespace App\ProviderStatusData;

use App\ProviderStatusData\Fetchers\Cpg;
use App\ProviderStatusData\Fetchers\Cyc;
use App\ProviderStatusData\Fetchers\EcarNi;
use App\ProviderStatusData\Fetchers\Ecotricity;
use App\ProviderStatusData\Fetchers\Engenie;
use App\ProviderStatusData\Fetchers\Esbie;
use App\ProviderStatusData\Fetchers\Nissan;
use App\ProviderStatusData\Fetchers\Podpoint;
use App\ProviderStatusData\Fetchers\Polar;
use App\ProviderStatusData\Fetchers\Tesla;

class Updater
{
	public function run()
	{
        collect([
            new Cpg(),
            new Cyc(),
            new EcarNi(),
            new Ecotricity(),
            new Engenie(),
            new Esbie(),
            new Nissan(),
            new Podpoint(),
            new Polar(),
            new Tesla(),
        ])->each(function($fetcher){
            $fetcher->run();
        });
	}

}
