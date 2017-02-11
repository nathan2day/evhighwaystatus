<?php

namespace App\ProviderStatusData\Fetchers;

use App\ProviderStatusData\Traits\OpenChargeMap;

class Cpg extends Fetcher
{
    use OpenChargeMap;

    protected $operatorId = 150;
    protected $operator = 'CPG';
}