<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Parsers\Cyc as CycParser;

class Cyc extends Fetcher
{
    protected $uri = 'https://secure.chargeyourcar.org.uk/map-api-iframe';

    protected function setParser()
    {
        $this->parser = new CycParser;
    }

}