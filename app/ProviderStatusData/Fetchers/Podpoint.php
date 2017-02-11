<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Traits\OpenChargeMap;

class Podpoint extends Fetcher
{
    use OpenChargeMap;

    protected $operatorId = 3;
    protected $operator = 'PodPoint';
}