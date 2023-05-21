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
        Schema::table('provider_budget_item', function (Blueprint $table) {

            $table->bigInteger('event_transport_opt_id')->unsigned()->nullable();

            $table->foreign('event_transport_opt_id')->references('id')->on('event_transport_opt')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_budget_item', function (Blueprint $table) {
            //
        });
    }
};
