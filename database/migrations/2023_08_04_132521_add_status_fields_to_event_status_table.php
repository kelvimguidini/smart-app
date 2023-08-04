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
            $table->string('status_u_hotel')->default('N');
            $table->string('status_u_transport')->default('N');
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
            $table->dropColumn('status_u_hotel');
            $table->dropColumn('status_u_transport');
        });
    }
};
