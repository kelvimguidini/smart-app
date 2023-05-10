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
        Schema::create('provider_budget', function (Blueprint $table) {
            $table->id();


            $table->decimal('hosting_fee_hotel')->nullable();
            $table->decimal('iss_fee_hotel')->nullable();
            $table->decimal('iva_fee_hotel')->nullable();
            $table->string('comment_hotel')->nullable();
            $table->bigInteger('event_hotel_id')->unsigned()->nullable();


            $table->decimal('hosting_fee_ab')->nullable();
            $table->decimal('iss_fee_ab')->nullable();
            $table->decimal('iva_fee_ab')->nullable();
            $table->string('comment_ab')->nullable();
            $table->bigInteger('event_ab_id')->unsigned()->nullable();

            $table->decimal('hosting_fee_add')->nullable();
            $table->decimal('iss_fee_add')->nullable();
            $table->decimal('iva_fee_add')->nullable();
            $table->string('comment_add')->nullable();
            $table->bigInteger('event_add_id')->unsigned()->nullable();

            $table->decimal('hosting_fee_hall')->nullable();
            $table->decimal('iss_fee_hall')->nullable();
            $table->decimal('iva_fee_hall')->nullable();
            $table->string('comment_hall')->nullable();
            $table->bigInteger('event_hall_id')->unsigned()->nullable();


            $table->softDeletes();
            $table->timestamps();


            $table->foreign('event_hotel_id')->references('id')->on('event_hotel')->onDelete('cascade');
            $table->foreign('event_ab_id')->references('id')->on('event_ab')->onDelete('cascade');
            $table->foreign('event_hall_id')->references('id')->on('event_hall')->onDelete('cascade');
            $table->foreign('event_add_id')->references('id')->on('event_add')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_budget');
    }
};
