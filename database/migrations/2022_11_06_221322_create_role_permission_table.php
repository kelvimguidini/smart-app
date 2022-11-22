<?php

use App\Http\Middleware\Constants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('role_permission', function (Blueprint $table) {
            $table->bigInteger('permission_id')->unsigned()->index();
            $table->bigInteger('role_id')->unsigned()->index();
            $table->foreign('permission_id')->references('id')->on('permission');
            $table->foreign('role_id')->references('id')->on('roles');
        });

        // Insert some stuff
        foreach (Constants::PERMISSIONS as $permission) {

            DB::table('role_permission')->insert(
                array(
                    'permission_id' => DB::table('permission')->select('id')->where('name', $permission->name)->first()->id,
                    'role_id' => DB::table('roles')->select('id')->where('name', 'Administrador')->first()->id
                )
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_permission');
    }
};
