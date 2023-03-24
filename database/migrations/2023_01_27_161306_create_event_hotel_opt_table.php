<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventHotelOptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_hotel_opt', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('event_hotel_id')->unsigned()->index();
            $table->bigInteger('apto_hotel_id')->unsigned()->index();
            $table->bigInteger('category_hotel_id')->unsigned()->index();

            $table->bigInteger('broker_id')->unsigned()->index();
            $table->bigInteger('regime_id')->unsigned()->index();
            $table->bigInteger('purpose_id')->unsigned()->index();

            $table->timestamp('in');
            $table->timestamp('out');
            $table->bigInteger('count');
            $table->decimal('kickback');

            //PROPOSTA
            $table->decimal('received_proposal');
            $table->decimal('received_proposal_percent');
            // COMPARATIVO
            $table->decimal('compare_trivago');
            $table->decimal('compare_website_htl');
            $table->decimal('compare_omnibess');

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('event_hotel_id')->references('id')->on('event_hotel')->onDelete('cascade');
            $table->foreign('apto_hotel_id')->references('id')->on('apto')->onDelete('cascade');
            $table->foreign('category_hotel_id')->references('id')->on('category')->onDelete('cascade');

            $table->foreign('broker_id')->references('id')->on('broker')->onDelete('cascade');
            $table->foreign('regime_id')->references('id')->on('regime')->onDelete('cascade');
            $table->foreign('purpose_id')->references('id')->on('purpose')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_hotel_opt');
    }
}
