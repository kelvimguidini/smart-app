<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventHallOptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_hall_opt', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('event_hall_id')->unsigned()->index();

            $table->bigInteger('broker_id')->unsigned()->index();
            $table->bigInteger('service_id')->unsigned()->index();
            $table->bigInteger('purpose_id')->unsigned()->index();
            $table->string('name');
            $table->integer('m2');
            $table->string('pax');

            $table->timestamp('in');
            $table->timestamp('out');
            $table->bigInteger('count');
            $table->decimal('kickback');

            //PROPOSTA
            $table->decimal('received_proposal');
            $table->decimal('received_proposal_percent');


            $table->softDeletes();
            $table->timestamps();


            $table->foreign('event_hall_id')->references('id')->on('event_hall')->onDelete('cascade');
            $table->foreign('broker_id')->references('id')->on('broker')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('service_hall')->onDelete('cascade');
            $table->foreign('purpose_id')->references('id')->on('purpose_hall')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_hall_opt');
    }
}
