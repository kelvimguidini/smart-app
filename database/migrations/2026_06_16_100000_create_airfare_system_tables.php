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
        // 1. Companhias Aéreas (airfare_airlines)
        Schema::create('airfare_airlines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Regras/Opções de Bagagem (airfare_baggages)
        Schema::create('airfare_baggages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // 3. Tipos de Cabine (airfare_cabins)
        Schema::create('airfare_cabins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // 4. Fornecedores de Aéreo (provider_airfare)
        Schema::create('provider_airfare', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('contact');
            $table->string('phone');
            $table->string('email');
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

        // 5. Vínculo de Fornecedor Aéreo no Evento (event_airfare)
        Schema::create('event_airfare', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('airfare_id')->index();
            $table->unsignedBigInteger('currency_id')->index();

            // taxas/impostos
            $table->decimal('iss_percent', 15, 2)->nullable()->default(0);
            $table->decimal('service_percent', 15, 2)->nullable()->default(0);
            $table->decimal('iva_percent', 15, 2)->nullable()->default(0);
            $table->decimal('iof', 15, 2)->nullable()->default(0);
            $table->decimal('taxa_4bts', 15, 2)->nullable()->default(0);
            $table->decimal('service_charge', 15, 2)->nullable()->default(0);

            $table->boolean('invoice')->default(false);
            $table->string('internal_observation')->nullable();
            $table->string('customer_observation')->nullable();

            $table->boolean('sended_mail')->default(false);
            $table->string('sended_mail_link')->nullable();
            $table->string('token_budget')->nullable();
            $table->date('deadline_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->integer('order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('event');
            $table->foreign('airfare_id')->references('id')->on('provider_airfare');
            $table->foreign('currency_id')->references('id')->on('currency');
        });

        // 6. Opções/Cotações de Aéreo (event_airfare_opt)
        Schema::create('event_airfare_opt', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_airfare_id')->index();

            // Ida (Outbound)
            $table->unsignedBigInteger('outbound_airline_id')->nullable()->index();
            $table->string('outbound_flight_number')->nullable();
            $table->string('outbound_class')->nullable();
            $table->date('outbound_date')->nullable();
            $table->string('outbound_origin')->nullable();
            $table->string('outbound_destination')->nullable();
            $table->string('outbound_departure_time')->nullable();
            $table->string('outbound_arrival_time')->nullable();
            $table->string('outbound_connection_details')->nullable();

            // Volta (Inbound)
            $table->unsignedBigInteger('inbound_airline_id')->nullable()->index();
            $table->string('inbound_flight_number')->nullable();
            $table->string('inbound_class')->nullable();
            $table->date('inbound_date')->nullable();
            $table->string('inbound_origin')->nullable();
            $table->string('inbound_destination')->nullable();
            $table->string('inbound_departure_time')->nullable();
            $table->string('inbound_arrival_time')->nullable();
            $table->string('inbound_connection_details')->nullable();

            // Dados Financeiros
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->decimal('received_proposal', 15, 2)->nullable()->default(0);
            $table->decimal('received_proposal_percent', 15, 2)->nullable()->default(0);
            $table->decimal('kickback', 15, 2)->nullable()->default(0);
            $table->decimal('compare_website', 15, 2)->nullable()->default(0);
            $table->decimal('compare_client', 15, 2)->nullable()->default(0);
            $table->decimal('count', 10, 2)->nullable()->default(1);

            // Regras
            $table->unsignedBigInteger('baggage_id')->nullable()->index();
            $table->unsignedBigInteger('cabin_id')->nullable()->index();
            $table->string('status')->nullable();
            $table->string('observation')->nullable();
            $table->integer('order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('event_airfare_id')->references('id')->on('event_airfare')->onDelete('cascade');
            $table->foreign('outbound_airline_id')->references('id')->on('airfare_airlines');
            $table->foreign('inbound_airline_id')->references('id')->on('airfare_airlines');
            $table->foreign('currency_id')->references('id')->on('currency');
            $table->foreign('baggage_id')->references('id')->on('airfare_baggages');
            $table->foreign('cabin_id')->references('id')->on('airfare_cabins');
        });

        // 7. Ficha de Voo (event_airfare_passengers)
        Schema::create('event_airfare_passengers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_airfare_id')->index();

            // Passageiro
            $table->string('name');
            $table->string('document')->nullable();
            $table->date('passport_validity')->nullable();
            $table->date('birth_date')->nullable();

            // Ida
            $table->date('outbound_date')->nullable();
            $table->string('outbound_origin')->nullable();
            $table->string('outbound_destination')->nullable();
            $table->string('outbound_departure')->nullable();
            $table->string('outbound_arrival')->nullable();

            // Volta
            $table->date('inbound_date')->nullable();
            $table->string('inbound_origin')->nullable();
            $table->string('inbound_destination')->nullable();
            $table->string('inbound_departure')->nullable();
            $table->string('inbound_arrival')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('event_airfare_id')->references('id')->on('event_airfare')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_airfare_passengers');
        Schema::dropIfExists('event_airfare_opt');
        Schema::dropIfExists('event_airfare');
        Schema::dropIfExists('provider_airfare');
        Schema::dropIfExists('airfare_cabins');
        Schema::dropIfExists('airfare_baggages');
        Schema::dropIfExists('airfare_airlines');
    }
};
