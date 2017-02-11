<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Traits\OpenChargeMap;

class Engenie extends Fetcher
{
    use OpenChargeMap;

    protected $operatorId = 203;
    protected $operator = 'Engenie';
}