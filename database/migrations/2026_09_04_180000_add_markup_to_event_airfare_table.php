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
        Schema::table('event_airfare', function (Blueprint $table) {
            if (!Schema::hasColumn('event_airfare', 'markup')) {
                $table->decimal('markup', 8, 4)->default(0.75)->after('total_net_sem_4bts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_airfare', function (Blueprint $table) {
            if (Schema::hasColumn('event_airfare', 'markup')) {
                $table->dropColumn('markup');
            }
        });
    }
};
