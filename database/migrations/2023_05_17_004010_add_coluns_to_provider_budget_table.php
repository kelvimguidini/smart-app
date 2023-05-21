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
        Schema::table('provider_budget', function (Blueprint $table) {
            $table->decimal('hosting_fee_transport')->nullable();
            $table->decimal('iss_fee_transport')->nullable();
            $table->decimal('iva_fee_transport')->nullable();
            $table->string('comment_transport')->nullable();
            $table->bigInteger('event_transport_id')->unsigned()->nullable();


            $table->foreign('event_transport_id')->references('id')->on('event_transport')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_budget', function (Blueprint $table) {
            //
        });
    }
};
