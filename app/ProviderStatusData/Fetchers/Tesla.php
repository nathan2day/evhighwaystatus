<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Parsers\Tesla as TeslaParser;

class Tesla extends Fetcher
{
    protected $uri = 'https://www.teslamotors.com/en_GB/findus#';

    protected function setParser()
    {
        $this->parser = new TeslaParser();
    }

}