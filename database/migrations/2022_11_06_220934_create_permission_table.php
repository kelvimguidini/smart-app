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
        Schema::create('permission', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('name');
            $table->timestamps();
        });

        DB::table('permission')->insert(
            array('name' => "user_create", "title" => "Criar Usuário")
        );
        DB::table('permission')->insert(
            array('name' => "user_update", "title" => "Editar Usuário")
        );
        DB::table('permission')->insert(
            array('name' => "user_delete", "title" => "Remover Usuário")
        );
        DB::table('permission')->insert(
            array('name' => "role_admin", "title" => "Administrar Perfil"),
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission');
    }
};
