<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEventTableMakeOperatorsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event', function (Blueprint $table) {
            $table->bigInteger('hotel_operator')->unsigned()->nullable()->change();
            $table->bigInteger('air_operator')->unsigned()->nullable()->change();
            $table->bigInteger('land_operator')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event', function (Blueprint $table) {
            $table->bigInteger('hotel_operator')->unsigned()->change();
            $table->bigInteger('air_operator')->unsigned()->change();
            $table->bigInteger('land_operator')->unsigned()->change();
        });
    }
}
