<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'event_transport_opt',
            'event_hall_opt',
            'event_hotel_opt',
            'event_add_opt',
            'event_ab_opt',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->decimal('count', 10, 2)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'event_transport_opt',
            'event_hall_opt',
            'event_hotel_opt',
            'event_add_opt',
            'event_ab_opt',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('count')->change();
            });
        }
    }
};
