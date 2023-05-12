<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Middleware\Constants;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class IncrementalPermissionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        foreach (Constants::PERMISSIONS as $p) {
            if (!DB::table('permission')->where('name', $p['name'])->exists()) {
                DB::table('permission')->insert(
                    array(
                        'name' => $p['name'],
                        "title" => $p['title']
                    )
                );

                DB::table('role_permission')->insert(
                    array(
                        'permission_id' => DB::table('permission')->select('id')->where('name',  $p['name'])->first()->id,
                        'role_id' => DB::table('roles')->select('id')->where('name', 'Administrador')->first()->id
                    )
                );
            }
        }
    }
}
