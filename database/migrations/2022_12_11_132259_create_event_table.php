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
        Schema::create('event', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('requester');
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->string('sector');
            $table->string('pax_base');
            $table->string('cost_center');
            $table->timestamp('date');
            $table->timestamp('date_final');
            $table->bigInteger('crd_id')->unsigned()->nullable();
            $table->bigInteger('hotel_operator')->unsigned()->nullable();
            $table->bigInteger('air_operator')->unsigned()->nullable();
            $table->bigInteger('land_operator')->unsigned()->nullable();


            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('NO ACTION');
            $table->foreign('crd_id')->references('id')->on('crd')->onDelete('NO ACTION');
            $table->foreign('hotel_operator')->references('id')->on('users')->onDelete('NO ACTION');
            $table->foreign('air_operator')->references('id')->on('users')->onDelete('NO ACTION');
            $table->foreign('land_operator')->references('id')->on('users')->onDelete('NO ACTION');
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
        Schema::dropIfExists('event');
    }
};
