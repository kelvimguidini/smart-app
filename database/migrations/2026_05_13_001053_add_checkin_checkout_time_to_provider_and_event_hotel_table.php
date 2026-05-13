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
        Schema::table('provider', function (Blueprint $table) {
            $table->string('checkin_time')->nullable();
            $table->string('checkin_time_end')->nullable();
            $table->string('checkout_time')->nullable();
            $table->string('checkout_time_end')->nullable();
        });

        Schema::table('event_hotel', function (Blueprint $table) {
            $table->string('checkin_time')->nullable();
            $table->string('checkin_time_end')->nullable();
            $table->string('checkout_time')->nullable();
            $table->string('checkout_time_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider', function (Blueprint $table) {
            $table->dropColumn(['checkin_time', 'checkin_time_end', 'checkout_time', 'checkout_time_end']);
        });

        Schema::table('event_hotel', function (Blueprint $table) {
            $table->dropColumn(['checkin_time', 'checkin_time_end', 'checkout_time', 'checkout_time_end']);
        });
    }
};
