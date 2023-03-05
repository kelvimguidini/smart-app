<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventAbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_ab', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('ab_id')->unsigned()->index();
            $table->bigInteger('event_id')->unsigned()->index();

            //impostos
            $table->decimal('iss_percent');
            $table->decimal('service_percent');
            $table->decimal('iva_percent');

            $table->bigInteger('currency_id')->unsigned()->index();

            $table->boolean('invoice');
            $table->string('internal_observation')->nullable();
            $table->string('customer_observation')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('ab_id')->references('id')->on('provider');
            $table->foreign('event_id')->references('id')->on('event');

            $table->foreign('currency_id')->references('id')->on('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_ab');
    }
}
