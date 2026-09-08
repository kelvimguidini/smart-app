<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('event_airfare')) {
            // Garante que dados de aircraft sejam preservados em equipment caso este esteja nulo
            if (Schema::hasColumn('event_airfare', 'aircraft') && Schema::hasColumn('event_airfare', 'equipment')) {
                DB::table('event_airfare')
                    ->where(function($query) {
                        $query->whereNull('equipment')->orWhere('equipment', '');
                    })
                    ->whereNotNull('aircraft')
                    ->where('aircraft', '!=', '')
                    ->update(['equipment' => DB::raw('aircraft')]);
            }

            Schema::table('event_airfare', function (Blueprint $table) {
                $columnsToDrop = [
                    'aircraft',
                    'status_contrato',
                    'exchange_rate_brl',
                    'prazo_proposta',
                    'total_venda_sem_4bts',
                    'resultado_bruto',
                    'tt_net_brl',
                    'tt_venda_brl',
                    'total_taxa_embarque',
                    'passengers_info',
                    'baggage_info',
                    'flight_time',
                ];

                foreach ($columnsToDrop as $col) {
                    if (Schema::hasColumn('event_airfare', $col)) {
                        $table->dropColumn($col);
                    }
                }
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
        if (Schema::hasTable('event_airfare')) {
            Schema::table('event_airfare', function (Blueprint $table) {
                $table->string('aircraft')->nullable();
                $table->string('status_contrato')->nullable();
                $table->decimal('exchange_rate_brl', 15, 4)->nullable()->default(1);
                $table->date('prazo_proposta')->nullable();
                $table->decimal('total_venda_sem_4bts', 15, 2)->nullable()->default(0);
                $table->decimal('resultado_bruto', 15, 2)->nullable()->default(0);
                $table->decimal('tt_net_brl', 15, 2)->nullable()->default(0);
                $table->decimal('tt_venda_brl', 15, 2)->nullable()->default(0);
                $table->decimal('total_taxa_embarque', 15, 2)->nullable()->default(0);
                $table->text('passengers_info')->nullable();
                $table->text('baggage_info')->nullable();
                $table->string('flight_time')->nullable();
            });
        }
    }
};
