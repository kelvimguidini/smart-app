<?php

use Illuminate\Database\Migrations\Migration;
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
        $permissionName = 'airfare_airport_admin';
        $permissionTitle = 'Administrar Aeroportos';

        $role = DB::table('roles')->where('name', 'Administrador')->first();

        $permission = DB::table('permission')->where('name', $permissionName)->first();
        if (!$permission) {
            $permissionId = DB::table('permission')->insertGetId([
                'name' => $permissionName,
                'title' => $permissionTitle,
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = DB::table('permission')->where('name', 'airfare_airport_admin')->first();
        if ($permission) {
            DB::table('role_permission')->where('permission_id', $permission->id)->delete();
            DB::table('permission')->where('id', $permission->id)->delete();
        }
    }
};
