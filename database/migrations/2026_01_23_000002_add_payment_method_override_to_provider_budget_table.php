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
            $table->enum('payment_method_override', ['CASH', 'CARTAO', 'INDEFINIDO'])->nullable()->after('approved');
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
            $table->dropColumn('payment_method_override');
        });
    }
};
