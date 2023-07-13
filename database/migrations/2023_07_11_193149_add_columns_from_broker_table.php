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
        Schema::table('broker', function (Blueprint $table) {
            $table->string('city');
            $table->string('contact');
            $table->string('phone');
            $table->string('email');
            $table->boolean('national');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('broker', function (Blueprint $table) {
            //
        });
    }
};
