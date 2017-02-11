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
        Type::create(['name' => 'CHAdeMO']);
        Type::create(['name' => 'CCS']);
        Type::create(['name' => 'AC (tethered)']);
        Type::create(['name' => 'AC (socket)']);    
        Type::create(['name' => '13A 3-Pin']);
        Type::create(['name' => 'Tesla SC']);
        Type::create(['name' => 'Tesla Dest.']);
    }
}
