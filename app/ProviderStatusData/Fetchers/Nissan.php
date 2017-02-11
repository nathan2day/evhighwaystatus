<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Traits\OpenChargeMap;

class Nissan extends Fetcher
{
    use OpenChargeMap;

    protected $operatorId = 50;
    protected $operator = 'Nissan';
}