<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AirfareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('airfare_airlines')->insert([
            ['name' => 'LATAM', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GOL', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AZUL', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('airfare_baggages')->insert([
            ['name' => 'MÃO (10KG)', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '1 X 23KG', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2 X 23KG', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('airfare_cabins')->insert([
            ['name' => 'ECONÔMICA', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ECONÔMICA PREMIUM', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EXECUTIVA', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PRIMEIRA CLASSE', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
