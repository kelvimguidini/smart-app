<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('broker_transport')->insert([
            ['name' => '4BTS'],
            ['name' => 'Ossa'],
            ['name' => 'Pluralis'],
            ['name' => 'Unidas'],
            ['name' => 'Movida'],
            ['name' => '4BTS USA'],
            ['name' => '4BTS EUR'],
            ['name' => 'CATAR'],
            ['name' => 'Master'],
            ['name' => 'Transmais'],
        ]);


        DB::table('car_model')->insert([
            ['name' => 'Luxo'],
            ['name' => 'Passageiros'],
            ['name' => 'Carga'],
            ['name' => 'Executivo'],
            ['name' => 'Importado'],
            ['name' => 'até 2 Ton'],
            ['name' => 'Ate 5 TON'],
        ]);

        DB::table('transport_service')->insert([
            ['name' => 'Transfer in'],
            ['name' => 'Transfer out'],
            ['name' => 'City Tour'],
            ['name' => 'Dia de Jogo'],
            ['name' => 'Treino'],
            ['name' => 'Dia Todo (8h)'],
            ['name' => '1/2 Dia (4H)'],
            ['name' => 'Dia Todo (10h)'],
        ]);


        DB::table('car_brand')->insert([
            ['name' => 'Mercedes'],
            ['name' => 'BMW'],
            ['name' => 'LEXUS'],
            ['name' => 'TOYOTA'],
            ['name' => 'Indiferente']
        ]);
    }
}
