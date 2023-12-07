<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusHistorySeeder extends Seeder
{
    public function run()
    {
        // Get the user ID
        $userId = DB::table('users')->select('id')->first()->id;

        // Define the tables to loop through
        $tables = ['event_ab', 'event_add', 'event_hall', 'event_hotel', 'event_transport'];

        // Loop through each table
        foreach ($tables as $table) {
            // Get all records from the current table
            $records = DB::table($table)->get();

            // Loop through each record and insert into status_history
            foreach ($records as $record) {
                DB::table('status_history')->insert([
                    'status' => 'created',
                    'user_id' => $userId,
                    'table' => $table . "s",
                    'table_id' => $record->id,
                    'observation' => '',
                    'created_at' => $record->created_at,
                ]);
            }
        }
    }
}
