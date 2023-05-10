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
        Schema::table('event_hotel', function (Blueprint $table) {
            $table->boolean('sended_mail_link')->default(false);
            $table->string('token_budget')->nullable();
        });

        Schema::table('event_ab', function (Blueprint $table) {
            $table->boolean('sended_mail_link')->default(false);
            $table->string('token_budget')->nullable();
        });

        Schema::table('event_hall', function (Blueprint $table) {
            $table->boolean('sended_mail_link')->default(false);
            $table->string('token_budget')->nullable();
        });

        Schema::table('event_add', function (Blueprint $table) {
            $table->boolean('sended_mail_link')->default(false);
            $table->string('token_budget')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
