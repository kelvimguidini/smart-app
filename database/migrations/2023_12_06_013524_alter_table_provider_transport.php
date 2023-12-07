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
        Schema::table('provider_transport', function (Blueprint $table) {
            // Remover o campo 'city'
            $table->dropColumn('city');

            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id')->references('id')->on('city');
        });
    }

    public function down()
    {
        Schema::table('provider_transport', function (Blueprint $table) {
            // Reverter as alterações no método down, caso necessário
            $table->string('city')->nullable();
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });
    }
};
