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
        Schema::table('proposal_histories', function (Blueprint $table) {
            $table->boolean('restored')->default(false)->after('action');
            $table->timestamp('restored_at')->nullable()->after('restored');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proposal_histories', function (Blueprint $table) {
            $table->dropColumn(['restored', 'restored_at']);
        });
    }
};
