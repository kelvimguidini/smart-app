<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hotel Opt
        Schema::table('event_hotel_opt', function (Blueprint $table) {
            $table->decimal('received_proposal')->nullable()->change();
            $table->decimal('received_proposal_percent')->nullable()->change();
            $table->decimal('kickback')->nullable()->change();
            $table->bigInteger('count')->nullable()->change();
            $table->decimal('compare_trivago')->nullable()->change();
            $table->decimal('compare_website_htl')->nullable()->change();
            $table->decimal('compare_omnibess')->nullable()->change();
        });
        // AB Opt
        Schema::table('event_ab_opt', function (Blueprint $table) {
            $table->decimal('received_proposal')->nullable()->change();
            $table->decimal('received_proposal_percent')->nullable()->change();
            $table->decimal('kickback')->nullable()->change();
            $table->bigInteger('count')->nullable()->change();
        });
        // Add Opt
        Schema::table('event_add_opt', function (Blueprint $table) {
            $table->decimal('received_proposal')->nullable()->change();
            $table->decimal('received_proposal_percent')->nullable()->change();
            $table->decimal('kickback')->nullable()->change();
            $table->bigInteger('count')->nullable()->change();
        });
        // Hall Opt
        Schema::table('event_hall_opt', function (Blueprint $table) {
            $table->decimal('received_proposal')->nullable()->change();
            $table->decimal('received_proposal_percent')->nullable()->change();
            $table->decimal('kickback')->nullable()->change();
            $table->bigInteger('count')->nullable()->change();
        });
        // Transport Opt
        Schema::table('event_transport_opt', function (Blueprint $table) {
            $table->decimal('received_proposal')->nullable()->change();
            $table->decimal('received_proposal_percent')->nullable()->change();
            $table->decimal('kickback')->nullable()->change();
            $table->bigInteger('count')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration for nullable change
    }
};
