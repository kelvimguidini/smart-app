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
        Schema::create('event_status', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('event_id')->unsigned()->index();

            $table->string('observation_hotel')->nullable();
            $table->string('observation_transport')->nullable();

            $table->timestamp('request_hotel')->nullable();
            $table->timestamp('provider_order_hotel')->nullable();
            $table->timestamp('briefing_hotel')->nullable();
            $table->timestamp('response_hotel')->nullable();
            $table->timestamp('pricing_hotel')->nullable();
            $table->timestamp('custumer_send_hotel')->nullable();
            $table->timestamp('change_hotel')->nullable();
            $table->timestamp('done_hotel')->nullable();

            $table->string('status_hotel')->nullable();

            $table->timestamp('request_transport')->nullable();
            $table->timestamp('provider_order_transport')->nullable();
            $table->timestamp('briefing_transport')->nullable();
            $table->timestamp('response_transport')->nullable();
            $table->timestamp('pricing_transport')->nullable();
            $table->timestamp('custumer_send_transport')->nullable();
            $table->timestamp('change_transport')->nullable();
            $table->timestamp('done_transport')->nullable();

            $table->string('status_transport')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('event')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_states');
    }
};
