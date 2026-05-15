<?php

namespace App\Domains\Auth\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::withoutGlobalScope('active')->where('email', $email)->first();
    }

    public function findWithRoles(int $id): ?User
    {
        return User::withoutGlobalScope('active')->with('roles')->find($id);
    }

    public function allWithRolesAndInactive(): Collection
    {
        return User::withoutGlobalScope('active')->with('roles')->get();
    }

    public function allNonApi(): Collection
    {
        return User::where('type', '!=', 'api')->get();
    }

    public function create(array $data, array $roleIds = []): User
    {
        return DB::transaction(function () use ($data, $roleIds) {
            $user = User::create($data);
            foreach ($roleIds as $roleId) {
                DB::table('user_role')->insert(['user_id' => $user->id, 'role_id' => $roleId]);
            }
            return $user;
        });
    }

    public function update(int $id, array $data, array $roleIds = []): User
    {
        return DB::transaction(function () use ($id, $data, $roleIds) {
            $user = User::withoutGlobalScope('active')->findOrFail($id);
            $user->update($data);

            if (!empty($roleIds)) {
                DB::table('user_role')->where('user_id', $id)->delete();
                foreach ($roleIds as $roleId) {
                    DB::table('user_role')->insert(['user_id' => $id, 'role_id' => $roleId]);
                }
            }

            return $user;
        });
    }

    public function delete(int $id): bool
    {
        return User::withoutGlobalScope('active')->findOrFail($id)->delete();
    }

    public function activate(int $id): bool
    {
        return User::withoutGlobalScope('active')->findOrFail($id)->activate();
    }

    public function deactivate(int $id): bool
    {
        return User::withoutGlobalScope('active')->findOrFail($id)->deactivate();
    }

    public function removeRole(int $userId, int $roleId): bool
    {
        return DB::table('user_role')->where([
            ['user_id', '=', $userId],
            ['role_id', '=', $roleId],
        ])->delete() > 0;
    }
}
