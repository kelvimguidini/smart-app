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
        Schema::create('proposal_histories', function (Blueprint $table) {
            $table->id();
            $table->string('table_name'); // Ex: event_transport_opt
            $table->unsignedBigInteger('record_id'); // ID do registro alterado
            $table->json('old_data')->nullable(); // Dados antes da mudança
            $table->json('new_data')->nullable(); // Dados após a mudança
            $table->string('action'); // created, updated, deleted
            $table->unsignedBigInteger('user_id')->nullable(); // quem fez
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposal_histories');
    }
};
