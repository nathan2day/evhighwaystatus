<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Traits\OpenChargeMap;

class Esbie extends Fetcher
{
    use OpenChargeMap;

    protected $operatorId = 22;
    protected $operator = 'ESBie';
}