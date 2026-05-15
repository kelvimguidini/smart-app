<?php

namespace App\Domains\Auth\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function allWithPermissions(): Collection;
    public function find(int $id): ?Role;
    public function saveRole(array $data, array $permissionNames, ?int $id = null): Role;
    public function delete(int $id): bool;
    public function removePermission(int $roleId, int $permissionId): bool;
}
