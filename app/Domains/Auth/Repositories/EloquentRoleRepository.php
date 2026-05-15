<?php

namespace App\Domains\Auth\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function allWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function find(int $id): ?Role
    {
        return Role::find($id);
    }

    public function saveRole(array $data, array $permissionNames, ?int $id = null): Role
    {
        return DB::transaction(function () use ($data, $permissionNames, $id) {
            $role = $id ? Role::findOrFail($id) : new Role();
            $role->fill($data);
            if (!$id) $role->active = true;
            $role->save();

            if ($id) {
                DB::table('role_permission')->where('role_id', $id)->delete();
            }

            foreach ($permissionNames as $name) {
                $permissionId = DB::table('permission')->where('name', $name)->value('id');
                if ($permissionId) {
                    DB::table('role_permission')->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $role->id
                    ]);
                }
            }

            return $role;
        });
    }

    public function delete(int $id): bool
    {
        $role = Role::findOrFail($id);
        return $role->delete();
    }

    public function removePermission(int $roleId, int $permissionId): bool
    {
        return DB::table('role_permission')->where([
            ['role_id', '=', $roleId],
            ['permission_id', '=', $permissionId],
        ])->delete() > 0;
    }
}
