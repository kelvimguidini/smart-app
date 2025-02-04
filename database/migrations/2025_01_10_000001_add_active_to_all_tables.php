<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveToAllTables extends Migration
{
    public $tables = [
        'car_brand',
        'apto',
        'vehicle',
        'transport_service',
        'service_type',
        'service_hall',
        'service_add',
        'service',
        'regime',
        'purpose',
        'purpose_hall',
        'provider_transport',
        'provider_services',
        'provider',
        'measure',
        'local',
        'frequency',
        'customer',
        'currency',
        'crd',
        'category',
        'city',
        'car_model',
        'broker_transport',
        'broker',
    ];

    public function up()
    {

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('active')->default(true);
            });
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('active');
            });
        }
    }
}
