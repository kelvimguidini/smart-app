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

            $table->string('email_reservations')->nullable();
            $table->string('contact_reservations')->nullable();
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

            $table->dropColumn('email_reservations');
            $table->dropColumn('contact_reservations');
        });
    }
};
