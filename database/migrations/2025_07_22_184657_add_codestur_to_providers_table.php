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
    public function up(): void
    {
        Schema::table('provider', function (Blueprint $table) {
            $table->string('codestur', 50)->nullable()->after('id');
        });
        Schema::table('provider_services', function (Blueprint $table) {
            $table->string('codestur', 50)->nullable()->after('id');
        });
        Schema::table('provider_transport', function (Blueprint $table) {
            $table->string('codestur', 50)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('provider', function (Blueprint $table) {
            $table->dropColumn('codestur');
        });
        Schema::table('provider_services', function (Blueprint $table) {
            $table->dropColumn('codestur');
        });
        Schema::table('provider_transport', function (Blueprint $table) {
            $table->dropColumn('codestur');
        });
    }
};
