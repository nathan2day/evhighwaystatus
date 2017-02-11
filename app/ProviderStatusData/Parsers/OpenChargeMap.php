<?php namespace App\ProviderStatusData\Parsers;

use App\ProviderStatusData\Interfaces\Parser;

class OpenChargeMap implements Parser
{

    private $operator;
    private $operatorid;

    public function parse($response)
    {
        $locations = [];
        $posts = json_decode($response,true);

        foreach ($posts as $post)
        {
            $charger = [];

            $charger["provider"]        = $this->operator;
            $charger["provider_openid"] = $this->operatorid;
            $charger["name"]            = $post["AddressInfo"]["Title"];
            $charger["lat"]             = $post["AddressInfo"]["Latitude"];
            $charger["lng"]             = $post["AddressInfo"]["Longitude"];
            $charger["postcode"]        = $post["AddressInfo"]["Postcode"];
            $charger["source"]["name"]  = "OpenChargeMap";
            $charger["source"]["url"]   = "http://openchargemap.org/site/poi/details/".$post["ID"];
            $charger['connectors']      = [];

            foreach ($post["Connections"] as $connection) {

                $connector = [];

                $connector["power"] = floatval($connection["PowerKW"]);

                switch($connection["StatusTypeID"]) {
                    case 50:
                        $connector["status"] = "online";
                        break;
                    case 100:
                        $connector["status"] = "offline";
                        break;
                    case 150:
                        $connector["status"] = "planned";
                        break;
                    case 75:
                        $connector["status"] = "unknown";
                        break;
                    default:
                        $connector["status"] = "unknown";
                        break;
                }

                switch($connection["ConnectionTypeID"]) {
                    case 2:
                        $connector["type"]["title"] = "CHAdeMO";
                        $connector["type"]["id"] = 1;
                        break;
                    case 3:
                        $connector["type"]["title"] = "13A 3-Pin";
                        $connector["type"]["id"] = 5;
                        break;
                    case 1036:
                        $connector["type"]["title"] = "AC (tethered)";
                        $connector["type"]["id"] = 3;
                        break;
                    case 25:
                        $connector["type"]["title"] = "AC (socket)";
                        $connector["type"]["id"] = 4;
                        break;
                    case 33:
                        $connector["type"]["title"] = "CCS";
                        $connector["type"]["id"] = 2;
                        break;
                    default:
                        $connector["type"]["title"] = "unknown - ".$connection["ConnectionTypeID"];
                        $connector["type"]["id"] = 0;
                        break;
                }
                $connector["quantity"] = $connection["Quantity"];
                $charger['connectors'][] = (object) $connector;
            }

            $locations[] = (object) $charger;

        }

        return $locations;
    }

    public function forProvider($operator, $operatorid)
    {
        $this->operator = $operator;
        $this->operatorid = $operatorid;

        return $this;
    }
}