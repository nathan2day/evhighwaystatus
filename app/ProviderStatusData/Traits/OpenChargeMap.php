<?php

namespace App\ProviderStatusData\Traits;

use App\ProviderStatusData\Parsers\OpenChargeMap as OcmParser;

trait OpenChargeMap
{
    protected function setParser()
    {
        $this->parser = (new OcmParser)->forProvider($this->operator,$this->operatorId);
    }

    protected function uri()
    {
        return "http://api.openchargemap.io/v2/poi/?output=json&countrycode=GB&operatorid=$this->operatorId&maxresults=5000";
    }
}