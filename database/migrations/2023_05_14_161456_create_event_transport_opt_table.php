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
        Schema::create('event_transport_opt', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('event_transport_id')->unsigned()->index();

            $table->bigInteger('broker_id')->unsigned()->index();
            $table->bigInteger('vehicle_id')->unsigned()->index();
            $table->bigInteger('model_id')->unsigned()->index();
            $table->bigInteger('service_id')->unsigned()->index();
            $table->bigInteger('brand_id')->unsigned()->index();
            $table->string('observation');

            $table->timestamp('in');
            $table->timestamp('out');
            $table->bigInteger('count');
            $table->decimal('kickback');

            //PROPOSTA
            $table->decimal('received_proposal');
            $table->decimal('received_proposal_percent');

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('event_transport_id')->references('id')->on('event_transport')->onDelete('cascade');

            $table->foreign('broker_id')->references('id')->on('broker')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicle')->onDelete('cascade');
            $table->foreign('model_id')->references('id')->on('car_model')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('transport_service')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('car_brand')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transport_opt');
    }
};
