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
        Schema::table('provider_budget', function (Blueprint $table) {
            $table->boolean('evaluated')->default(false);
            $table->boolean('approved')->default(false);
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->timestamp('approval_date')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_budget', function (Blueprint $table) {
            $table->dropColumn('evaluated');
            $table->dropColumn('approved');
        });
    }
};
