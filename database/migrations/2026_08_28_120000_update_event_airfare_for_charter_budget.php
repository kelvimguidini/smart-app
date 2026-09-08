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
        Schema::table('event_airfare', function (Blueprint $table) {
            $table->unsignedBigInteger('airfare_id')->nullable()->change();
            $table->unsignedBigInteger('airline_id')->nullable()->after('airfare_id');

            // Assentos
            $table->integer('pax_first')->default(0)->after('airline_id');
            $table->integer('pax_executiva')->default(0)->after('pax_first');
            $table->integer('pax_premium')->default(0)->after('pax_executiva');
            $table->integer('pax_economica')->default(0)->after('pax_premium');
            $table->integer('total_pax')->default(0)->after('pax_economica');

            // Prazos e Status Contrato
            $table->string('prazo_cia')->nullable()->after('total_pax');
            $table->string('status_contrato')->nullable()->after('prazo_cia');
            $table->date('prazo_proposta')->nullable()->after('status_contrato');

            // Inclusões do Fretamento
            $table->boolean('inc_taxa_embarque')->default(true)->after('prazo_proposta');
            $table->boolean('inc_servico_bordo')->default(true)->after('inc_taxa_embarque');
            $table->string('inc_porao')->nullable()->default('23 kg por pessoa')->after('inc_servico_bordo');
            $table->string('inc_bagagem_bordo')->nullable()->default('10 kg por pessoa')->after('inc_porao');
            $table->boolean('inc_sala_vip')->default(false)->after('inc_bagagem_bordo');
            $table->string('inc_fbo_origem')->nullable()->default('0')->after('inc_sala_vip');
            $table->string('inc_fbo_destino')->nullable()->default('0')->after('inc_fbo_origem');
            $table->boolean('inc_alteracao_nomes')->default(true)->after('inc_fbo_destino');

            // Financeiros Fretamento
            $table->decimal('taxa_embarque_unit', 15, 2)->default(0)->after('inc_alteracao_nomes');
            $table->decimal('total_taxa_embarque', 15, 2)->default(0)->after('taxa_embarque_unit');
            $table->decimal('total_net_sem_4bts', 15, 2)->default(0)->after('total_taxa_embarque');
            $table->decimal('total_venda_sem_4bts', 15, 2)->default(0)->after('total_net_sem_4bts');
            $table->decimal('resultado_bruto', 15, 2)->default(0)->after('total_venda_sem_4bts');
            $table->decimal('exchange_rate_brl', 15, 4)->default(1)->after('resultado_bruto');
            $table->decimal('tt_net_brl', 15, 2)->default(0)->after('exchange_rate_brl');
            $table->decimal('tt_venda_brl', 15, 2)->default(0)->after('tt_net_brl');

            $table->foreign('airline_id')->references('id')->on('airfare_airlines')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_airfare', function (Blueprint $table) {
            $table->dropForeign(['airline_id']);
            $table->dropColumn([
                'airline_id',
                'pax_first',
                'pax_executiva',
                'pax_premium',
                'pax_economica',
                'total_pax',
                'prazo_cia',
                'status_contrato',
                'prazo_proposta',
                'inc_taxa_embarque',
                'inc_servico_bordo',
                'inc_porao',
                'inc_bagagem_bordo',
                'inc_sala_vip',
                'inc_fbo_origem',
                'inc_fbo_destino',
                'inc_alteracao_nomes',
                'taxa_embarque_unit',
                'total_taxa_embarque',
                'total_net_sem_4bts',
                'total_venda_sem_4bts',
                'resultado_bruto',
                'exchange_rate_brl',
                'tt_net_brl',
                'tt_venda_brl',
            ]);
        });
    }
};
