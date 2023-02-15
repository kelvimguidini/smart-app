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
        Schema::create('crd', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number');
            $table->softDeletes();
            $table->timestamps();

            $table->bigInteger('customer_id')->unsigned();

            $table->foreign('customer_id')->references('id')->on('customer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crd');
    }
};
