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
        // Drop previous customer_metadata table if exists
        Schema::dropIfExists('customer_metadata');

        // Drop previous customer_metadata_admin permission
        $oldPermission = DB::table('permission')->where('name', 'customer_metadata_admin')->first();
        if ($oldPermission) {
            DB::table('role_permission')->where('permission_id', $oldPermission->id)->delete();
            DB::table('permission')->where('id', $oldPermission->id)->delete();
        }

        // Create customer_requesters
        if (!Schema::hasTable('customer_requesters')) {
            Schema::create('customer_requesters', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->bigInteger('customer_id')->unsigned()->index();
                $table->boolean('active')->default(true);
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
            });
        }

        // Create customer_sectors
        if (!Schema::hasTable('customer_sectors')) {
            Schema::create('customer_sectors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->bigInteger('customer_id')->unsigned()->index();
                $table->boolean('active')->default(true);
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
            });
        }

        // Create customer_cost_centers
        if (!Schema::hasTable('customer_cost_centers')) {
            Schema::create('customer_cost_centers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->bigInteger('customer_id')->unsigned()->index();
                $table->boolean('active')->default(true);
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
            });
        }

        // Permissions to insert
        $newPermissions = [
            ['name' => 'customer_requester_admin', 'title' => 'Administrar Solicitantes de Clientes'],
            ['name' => 'customer_sector_admin', 'title' => 'Administrar Setores de Clientes'],
            ['name' => 'customer_cost_center_admin', 'title' => 'Administrar Centros de Custo de Clientes']
        ];

        // Find Administrador role
        $role = DB::table('roles')->where('name', 'Administrador')->first();

        foreach ($newPermissions as $p) {
            $permission = DB::table('permission')->where('name', $p['name'])->first();
            if (!$permission) {
                $permissionId = DB::table('permission')->insertGetId([
                    'name' => $p['name'],
                    'title' => $p['title'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $permissionId = $permission->id;
            }

            if ($role) {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissions = ['customer_requester_admin', 'customer_sector_admin', 'customer_cost_center_admin'];
        foreach ($permissions as $name) {
            $permission = DB::table('permission')->where('name', $name)->first();
            if ($permission) {
                DB::table('role_permission')->where('permission_id', $permission->id)->delete();
                DB::table('permission')->where('id', $permission->id)->delete();
            }
        }

        Schema::dropIfExists('customer_cost_centers');
        Schema::dropIfExists('customer_sectors');
        Schema::dropIfExists('customer_requesters');
    }
};
