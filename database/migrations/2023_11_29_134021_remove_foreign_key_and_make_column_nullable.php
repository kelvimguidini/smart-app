<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveForeignKeyAndMakeColumnNullable extends Migration
{
    public function up()
    {
        // Remover a foreign key
        Schema::table('email_log', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
        });

        // Tornar a coluna provider_id nullable
        Schema::table('email_log', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id')->nullable()->change();
        });
    }

    public function down()
    {
        // Recriar a foreign key (se necessário)
        Schema::table('email_log', function (Blueprint $table) {
            $table->foreign('provider_id')->references('id')->on('providers');
        });

        // Tornar a coluna provider_id não nullable (se necessário)
        Schema::table('email_log', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id')->nullable(false)->change();
        });
    }
}
