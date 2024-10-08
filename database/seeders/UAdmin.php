<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Middleware\Constants;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UAdmin extends Seeder
{
    /**
     * Seed the application's database.
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
                'password' => Hash::make('Admin'),
                'email_verified_at' => Carbon::now()
            )
        );

        // Insert some stuff
        DB::table('roles')->insert(
            array(
                'name' => 'Administrador',
                'active' => true,
            )
        );


        // Insert some stuff
        DB::table('user_role')->insert(
            array(
                'user_id' => DB::table('users')->select('id')->where('email', 'admin@admin.com')->first()->id,
                'role_id' => DB::table('roles')->select('id')->where('name', 'Administrador')->first()->id
            )
        );
    }
}
