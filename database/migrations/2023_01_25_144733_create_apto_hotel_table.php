<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAptoHotelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apto_hotel', function (Blueprint $table) {
            $table->bigInteger('apto_id')->unsigned()->index();
            $table->bigInteger('hotel_id')->unsigned()->index();
            $table->foreign('apto_id')->references('id')->on('apto');
            $table->foreign('hotel_id')->references('id')->on('hotel')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apto_hotel');
    }
}
