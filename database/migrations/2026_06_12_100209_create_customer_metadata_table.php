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
        if (!Schema::hasTable('customer_metadata')) {
            Schema::create('customer_metadata', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('customer_id')->unsigned()->index();
                $table->string('type'); // 'requester', 'sector', 'cost_center'
                $table->string('value');
                $table->boolean('active')->default(true);

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
            });
        }

        // Insert new permission if not exists
        $permission = DB::table('permission')->where('name', 'customer_metadata_admin')->first();
        if (!$permission) {
            $permissionId = DB::table('permission')->insertGetId([
                'name' => 'customer_metadata_admin',
                'title' => 'Administrar Metadados de Clientes',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $permissionId = $permission->id;
        }

        // Find Administrador role
        $role = DB::table('roles')->where('name', 'Administrador')->first();
        if ($role) {
            // Check if role_permission already exists
            $exists = DB::table('role_permission')
                ->where('permission_id', $permissionId)
                ->where('role_id', $role->id)
                ->exists();
            if (!$exists) {
                DB::table('role_permission')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $role->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove permission
        $permission = DB::table('permission')->where('name', 'customer_metadata_admin')->first();
        if ($permission) {
            DB::table('role_permission')->where('permission_id', $permission->id)->delete();
            DB::table('permission')->where('id', $permission->id)->delete();
        }

        Schema::dropIfExists('customer_metadata');
    }
};
