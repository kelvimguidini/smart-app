<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBroker extends Migration
{
    public function up()
    {
        Schema::table('broker', function (Blueprint $table) {
            // Remover o campo 'city'
            $table->dropColumn('city');

            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id')->references('id')->on('city');
        });
    }

    public function down()
    {
        Schema::table('broker', function (Blueprint $table) {
            // Reverter as alterações no método down, caso necessário
            $table->string('city')->nullable();
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });
    }
}
