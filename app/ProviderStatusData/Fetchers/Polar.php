<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Parsers\Polar as PolarParser;

class Polar extends Fetcher
{
    protected $uri = 'https://polar-network.com/ajax/posts/';

    protected function setParser()
    {
        $this->parser = new PolarParser();
    }

}