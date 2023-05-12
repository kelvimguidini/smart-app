<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('vehicle')->insert([
            ['name' => 'Onibus'],
            ['name' => 'Micro Onibus'],
            ['name' => 'Van 15'],
            ['name' => 'Mini Van 7'],
            ['name' => 'Carro'],
            ['name' => 'Caminhão']
        ]);
    }
}
