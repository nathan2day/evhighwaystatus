<?php

use Illuminate\Database\Seeder;
use App\Type;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Type::create(['name' => 'CHAdeMO',         'power' =>  50]);
        Type::create(['name' => 'CCS',             'power' =>  50]);
        Type::create(['name' => 'AC (tethered)',   'power' =>  50]);
        Type::create(['name' => 'AC (socket)',     'power' =>   7]);    
        Type::create(['name' => '13A 3-Pin',       'power' =>   3]);
        Type::create(['name' => 'AC (medium)',     'power' =>  22]); 
        Type::create(['name' => 'Tesla',           'power' => 120]);
    }
}
