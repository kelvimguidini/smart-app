<?php

namespace Database\Seeders;

use App\Http\Middleware\Constants;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InitialSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert some stuff
        DB::table('users')->insert(
            array(
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('Admin')
            )
        );

        // Insert some stuff
        DB::table('roles')->insert(
            array(
                'name' => 'Administrador',
                'active' => true
            )
        );


        // Insert some stuff
        DB::table('user_role')->insert(
            array(
                'user_id' => DB::table('users')->select('id')->where('email', 'admin@admin.com')->first()->id,
                'role_id' => DB::table('roles')->select('id')->where('name', 'Administrador')->first()->id
            )
        );


        foreach (Constants::PERMISSIONS as $p) {
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
