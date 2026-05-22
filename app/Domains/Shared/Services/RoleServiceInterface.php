<?php

namespace App\Domains\Shared\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoleServiceInterface
{
    public function getPaginatedRoles(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function saveRole(array $data): Role;
    public function deleteRole(int $id): bool;
    public function removePermission(int $roleId, int $permissionId): bool;
}
