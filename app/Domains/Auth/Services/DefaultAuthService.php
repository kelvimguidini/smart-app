<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Auth\Repositories\RoleRepositoryInterface;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class DefaultAuthService implements AuthServiceInterface
{
    protected $userRepository;
    protected $roleRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RoleRepositoryInterface $roleRepository
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @inheritDoc
     */
    public function storeUser(array $data, ?int $id = null, array $roleIds = []): User
    {
        if ($id) {
            return $this->userRepository->update($id, [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'signature' => $data['signature'] ?? null,
            ], $roleIds);
        }

        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'signature' => $data['signature'] ?? null,
            'password' => Hash::make($data['password'] ?? 'qwerty'),
        ], $roleIds);

        $user->sendEmailVerificationNotification();

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function setUserStatus(int $id, bool $active): bool
    {
        return $active ? $this->userRepository->activate($id) : $this->userRepository->deactivate($id);
    }

    /**
     * @inheritDoc
     */
    public function removeUserRole(int $userId, int $roleId): bool
    {
        return $this->userRepository->removeRole($userId, $roleId);
    }

    /**
     * @inheritDoc
     */
    public function storeRole(array $data, ?int $id = null, array $permissionIds = []): Role
    {
        if ($id) {
            return $this->roleRepository->update($id, $data, $permissionIds);
        }
        return $this->roleRepository->create($data, $permissionIds);
    }
}
