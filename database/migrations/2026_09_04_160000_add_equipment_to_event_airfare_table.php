<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_airfare', function (Blueprint $table) {
            if (!Schema::hasColumn('event_airfare', 'equipment')) {
                $table->string('equipment')->nullable()->after('aircraft');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_airfare', function (Blueprint $table) {
            if (Schema::hasColumn('event_airfare', 'equipment')) {
                $table->dropColumn('equipment');
            }
        });
    }
};
