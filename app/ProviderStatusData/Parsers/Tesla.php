<?php namespace App\ProviderStatusData\Parsers;

use App\ProviderStatusData\Interfaces\Parser;

class Tesla implements Parser
{
    public function parse($response)
    {
        $locations = [];

        $response = substr($response, strpos($response,"var location_data =") + 20);
        $response = substr($response,0,strpos($response,"var production = true;") - 5);

        $posts = json_decode($response,true);

        foreach ($posts as $key => $location) {
            if ($location["country"] == "United Kingdom"){



                if (array_search("supercharger", $location["location_type"]) !== false ||
                    array_search("destination charger",$location["location_type"]) !== false )
                {
                    $charger = [];

                    $charger["name"] = $location["title"];
                    $charger["provider"] = 'Tesla';
                    $charger["lat"] = floatval($location["latitude"]);
                    $charger["lng"] = floatval($location["longitude"]);
                    $charger["postcode"] = $location["postal_code"];
                    $charger["source"]["name"] = "Tesla";
                    $charger["source"]["url"] = "https://www.teslamotors.com/en_GB/findus/";
                    $charger["provider_openid"] = 23;
                    $charger["connectors"] = [];

                    //find out how many superchargers, if it has them
                    if (array_search("supercharger", $location["location_type"]) !== false)
                    {
                        $chargerCount = substr($location["chargers"],strpos($location["chargers"],"Superchargers") - 2,1);

                        $connector = [];

                        $connector["power"] = 120;
                        $connector["quantity"] = intval($chargerCount);
                        $connector["status"] = "online";
                        $connector["type"]["title"] = "Tesla SC";
                        $connector["type"]["id"] = 6;

                        $charger['connectors'][] = (object) $connector;



                        array_push($locations,(object) $charger);
                    }

                    //find out how many destination chargers, if it has them
                    if (array_search("destination charger", $location["location_type"]) !== false)
                    {
                        $chargerCount = substr($location["chargers"],strpos($location["chargers"],"Tesla Connector") - 2,1);
                        $power = substr($location["chargers"],strpos($location["chargers"],"up to") + 6 ,20);
                        $power = substr($power, 0, strpos($power,"kW"));

                        $connector = [];

                        $connector["power"] = round(floatval($power),0,PHP_ROUND_HALF_DOWN);
                        $connector["quantity"] = intval($chargerCount);
                        $connector["status"] = "online";
                        $connector["type"]["title"] = "Tesla Dest.";
                        $connector["type"]["id"] = 7;

                        $charger['connectors'][] = (object) $connector;

                        array_push($locations,(object) $charger);

                    }

                }
            }
        }



        return $locations;
    }
}