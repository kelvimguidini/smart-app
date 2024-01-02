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
        Schema::table('event', function (Blueprint $table) {
            // Remover a obrigatoriedade da chave estrangeira
            $table->unsignedBigInteger('land_operator')->nullable()->change();
            $table->unsignedBigInteger('air_operator')->nullable()->change();
            $table->unsignedBigInteger('hotel_operator')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
