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
        Schema::table('event_airfare', function (Blueprint $table) {
            $table->string('aircraft')->nullable()->after('currency_id');
            $table->string('passengers_info')->nullable()->after('aircraft');
            $table->string('baggage_info')->nullable()->after('passengers_info');
            $table->string('flight_time')->nullable()->after('baggage_info');
            $table->text('photo_1')->nullable()->after('flight_time');
            $table->text('photo_2')->nullable()->after('photo_1');
            $table->text('photo_3')->nullable()->after('photo_2');
            $table->text('photo_4')->nullable()->after('photo_3');
            $table->text('observations')->nullable()->after('customer_observation');
            $table->text('notes')->nullable()->after('observations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_airfare', function (Blueprint $table) {
            $table->dropColumn([
                'aircraft',
                'passengers_info',
                'baggage_info',
                'flight_time',
                'photo_1',
                'photo_2',
                'photo_3',
                'photo_4',
                'observations',
                'notes',
            ]);
        });
    }
};
