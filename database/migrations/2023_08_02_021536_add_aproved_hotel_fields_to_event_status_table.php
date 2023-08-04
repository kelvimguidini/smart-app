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
        Schema::table('event_status', function (Blueprint $table) {
            $table->string('aproved_hotel')->nullable();
            $table->string('aproved_transport')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_status', function (Blueprint $table) {
            $table->dropColumn('aproved_hotel');
            $table->dropColumn('aproved_transport');
        });
    }
};
