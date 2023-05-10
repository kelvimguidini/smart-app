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
        Schema::create('provider_budget_item', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('provider_budget_id')->unsigned()->index();

            $table->bigInteger('event_hotel_opt_id')->unsigned()->nullable();
            $table->bigInteger('event_ab_opt_id')->unsigned()->nullable();
            $table->bigInteger('event_add_opt_id')->unsigned()->nullable();
            $table->bigInteger('event_hall_opt_id')->unsigned()->nullable();

            $table->decimal('comission');
            $table->decimal('value');
            $table->string('comment');

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('provider_budget_id')->references('id')->on('provider_budget')->onDelete('cascade');

            $table->foreign('event_hotel_opt_id')->references('id')->on('event_hotel_opt')->onDelete('cascade');
            $table->foreign('event_ab_opt_id')->references('id')->on('event_ab_opt')->onDelete('cascade');
            $table->foreign('event_hall_opt_id')->references('id')->on('event_hall_opt')->onDelete('cascade');
            $table->foreign('event_add_opt_id')->references('id')->on('event_add_opt')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_budget_item');
    }
};
