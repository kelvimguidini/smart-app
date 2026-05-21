<?php

namespace App\Domains\Auth\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findWithRoles(int $id): ?User;
    public function allWithRolesAndInactive(): Collection;
    public function allNonApi(): Collection;
    public function update(int $id, array $data, array $roleIds = []): User;
    public function create(array $data, array $roleIds = []): User;
    public function delete(int $id): bool;
    public function activate(int $id): bool;
    public function deactivate(int $id): bool;
    public function removeRole(int $userId, int $roleId): bool;
}
