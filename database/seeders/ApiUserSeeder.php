<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if the user already exists to prevent duplicate entries
        if (!DB::table('users')->where('email', 'apiuser@teste.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'API User',
                'email' => 'apiuser@teste.com',
                'password' => Hash::make('senha123'),
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'is_api_user' => true,
            ]);
        }
    }
}
