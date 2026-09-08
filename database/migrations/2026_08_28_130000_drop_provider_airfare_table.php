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
        if (Schema::hasTable('event_airfare') && Schema::hasColumn('event_airfare', 'airfare_id')) {
            Schema::table('event_airfare', function (Blueprint $table) {
                try {
                    $table->dropForeign(['airfare_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('airfare_id');
            });
        }

        Schema::dropIfExists('provider_airfare');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('provider_airfare', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('contact')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('national')->default(true);
            $table->decimal('iss_percent', 15, 2)->nullable()->default(0);
            $table->decimal('service_percent', 15, 2)->nullable()->default(0);
            $table->decimal('iva_percent', 15, 2)->nullable()->default(0);
            $table->boolean('active')->default(true);
            $table->string('codestur')->nullable();
            $table->string('payment_method')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('city');
        });
    }
};
