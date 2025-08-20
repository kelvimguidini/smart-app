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
        Schema::table('event_ab_opt', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
        Schema::table('event_add_opt', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
        Schema::table('event_hall_opt', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
        Schema::table('event_hotel_opt', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
        Schema::table('event_transport_opt', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_ab_opt', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('event_add_opt', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('event_hall_opt', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('event_hotel_opt', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('event_transport_opt', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
