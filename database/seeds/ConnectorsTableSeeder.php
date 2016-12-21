<?php

use Illuminate\Database\Seeder;
use App\Connector;
class ConnectorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Connector::create(['name' => 'CCS',             'power' => 50]);
        Connector::create(['name' => 'CHAdeMO',         'power' => 50]);
        Connector::create(['name' => 'AC (tethered)',   'power' => 50]);
        Connector::create(['name' => 'AC (socket)',     'power' =>  7]);
        Connector::create(['name' => 'AC (medium)',     'power' => 22]);
    }
}
