<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventAbOptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_ab_opt', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('event_ab_id')->unsigned()->index();

            $table->bigInteger('broker_id')->unsigned()->index();
            $table->bigInteger('service_id')->unsigned()->index();
            $table->bigInteger('service_type_id')->unsigned()->index();
            $table->bigInteger('local_id')->unsigned()->index();

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


            $table->foreign('event_ab_id')->references('id')->on('event_ab')->onDelete('cascade');
            $table->foreign('local_id')->references('id')->on('local')->onDelete('cascade');
            $table->foreign('broker_id')->references('id')->on('broker')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('service')->onDelete('cascade');
            $table->foreign('service_type_id')->references('id')->on('service_type')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_ab_opt');
    }
}
