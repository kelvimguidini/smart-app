<?php

namespace Database\Seeders;

use App\Http\Middleware\Constants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run()
    {
        foreach (Constants::CITIES as $p) {
            if (!DB::table('city')->where('name', $p['name'])->exists()) {
                DB::table('city')->insert(
                    array(
                        'name' => $p['name'],
                        "states" => $p['uf'],
                        "country" => "Brasil"
                    )
                );
            }
        }
    }
}
