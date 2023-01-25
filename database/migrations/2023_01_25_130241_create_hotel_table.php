<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('contact');
            $table->string('phone');
            $table->string('email');
            $table->boolean('national');

            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->bigInteger('apto_id')->unsigned()->nullable();

            $table->foreign('category_id')->references('id')->on('category')->onDelete('NO ACTION');
            $table->foreign('apto_id')->references('id')->on('apto')->onDelete('NO ACTION');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotel');
    }
}
