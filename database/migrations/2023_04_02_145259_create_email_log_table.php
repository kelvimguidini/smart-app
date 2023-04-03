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
        Schema::create('email_log', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('event_id')->unsigned()->index();
            $table->bigInteger('provider_id')->unsigned()->index();
            $table->bigInteger('sender_id')->unsigned()->index();
            $table->string('body');
            $table->binary('attachment');

            $table->timestamps();


            $table->foreign('event_id')->references('id')->on('event');
            $table->foreign('provider_id')->references('id')->on('provider');
            $table->foreign('sender_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_log');
    }
};
