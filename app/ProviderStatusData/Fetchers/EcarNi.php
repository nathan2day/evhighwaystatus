<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Traits\OpenChargeMap;

class EcarNi extends Fetcher
{
    use OpenChargeMap;

    protected $operatorId = 93;
    protected $operator = 'eCarNI';
}