<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\RoleRepositoryInterface;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class DefaultRoleService implements RoleServiceInterface
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function getPaginatedRoles(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->roleRepository->list($filters, $perPage);
    }

    public function saveRole(array $data): Role
    {
        DB::beginTransaction();
        try {
            if (isset($data['id']) && $data['id'] > 0) {
                // Remove existing active update override because it's handled properly by repository
                $updateData = ['name' => $data['name']];
                if (isset($data['active'])) {
                    $updateData['active'] = $data['active'];
                }
                $role = $this->roleRepository->update($data['id'], $updateData);
            } else {
                $createData = [
                    'name' => $data['name'],
                    'active' => $data['active'] ?? true
                ];
                $role = $this->roleRepository->create($createData);
            }

            // Sync Permissions
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $permissionIds = Permission::whereIn('name', $data['permissions'])->pluck('id')->toArray();
                $role->permissions()->sync($permissionIds);
            }

            DB::commit();

            return $role->load('permissions');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRole(int $id): bool
    {
        return $this->roleRepository->delete($id);
    }

    public function removePermission(int $roleId, int $permissionId): bool
    {
        DB::table('role_permission')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();

        return true;
    }
}
