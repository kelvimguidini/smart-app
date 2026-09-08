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
        // 1. Drop table event_airfare_passengers
        Schema::dropIfExists('event_airfare_passengers');

        // 2. Cleanup unused columns from event_airfare_opt
        if (Schema::hasTable('event_airfare_opt')) {
            Schema::table('event_airfare_opt', function (Blueprint $table) {
                // Drop foreign keys if existing
                $foreignKeysToDrop = [
                    'inbound_airline_id',
                    'currency_id',
                    'baggage_id',
                    'cabin_id'
                ];

                foreach ($foreignKeysToDrop as $fk) {
                    if (Schema::hasColumn('event_airfare_opt', $fk)) {
                        try {
                            $table->dropForeign([$fk]);
                        } catch (\Exception $e) {}
                    }
                }

                $columnsToDrop = [
                    'inbound_airline_id',
                    'inbound_flight_number',
                    'inbound_class',
                    'inbound_date',
                    'inbound_origin',
                    'inbound_destination',
                    'inbound_departure_time',
                    'inbound_arrival_time',
                    'inbound_connection_details',
                    'outbound_class',
                    'outbound_connection_details',
                    'currency_id',
                    'received_proposal',
                    'received_proposal_percent',
                    'kickback',
                    'compare_website',
                    'compare_client',
                    'count',
                    'baggage_id',
                    'cabin_id',
                    'status',
                    'observation',
                    'order'
                ];

                $existingColumnsToDrop = array_filter($columnsToDrop, function ($col) {
                    return Schema::hasColumn('event_airfare_opt', $col);
                });

                if (!empty($existingColumnsToDrop)) {
                    $table->dropColumn(array_values($existingColumnsToDrop));
                }
            });
        }

        // 3. Create airfare_airports table
        if (!Schema::hasTable('airfare_airports')) {
            Schema::create('airfare_airports', function (Blueprint $table) {
                $table->id();
                $table->string('iata_code', 3)->index();
                $table->string('name');
                $table->string('city');
                $table->string('state', 10)->nullable();
                $table->string('country', 100)->default('Brasil');
                $table->boolean('active')->default(true);
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('airfare_airports');
    }
};
