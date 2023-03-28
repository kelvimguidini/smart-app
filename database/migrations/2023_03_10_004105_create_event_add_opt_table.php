<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventAddOptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_add_opt', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('event_add_id')->unsigned()->index();

            $table->bigInteger('service_id')->unsigned()->index();
            $table->bigInteger('measure_id')->unsigned()->index();
            $table->bigInteger('frequency_id')->unsigned()->index();

            $table->integer('unit');
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


            $table->foreign('event_add_id')->references('id')->on('event_add')->onDelete('cascade');
            $table->foreign('measure_id')->references('id')->on('measure')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('service_add')->onDelete('cascade');
            $table->foreign('frequency_id')->references('id')->on('frequency')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_add_opt');
    }
}
