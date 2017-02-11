<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Parsers\Ecotricity as EcotricityParser;

class Ecotricity extends Fetcher
{
    protected $uri = 'http://www.ecotricity.co.uk/for-the-road/our-electric-highway';

    protected function setParser()
    {
        $this->parser = new EcotricityParser;
    }


}