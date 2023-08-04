<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelmentFieldsToEventStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_status', function (Blueprint $table) {
            $table->dateTime('cancelment_hotel')->nullable();
            $table->dateTime('cancelment_transport')->nullable();
            // Adicione outros campos, se necessário
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_status', function (Blueprint $table) {
            $table->dropColumn('cancelment_hotel');
            $table->dropColumn('cancelment_transport');
            // Remova outros campos, se necessário
        });
    }
}
