<?php

namespace App\Domains\Auth\Services;

use App\Models\User;
use App\Models\Role;

interface AuthServiceInterface
{
    /**
     * Salva ou atualiza um usuário e seus perfis.
     *
     * @param array $data
     * @param int|null $id
     * @param array $roleIds
     * @return User
     */
    public function storeUser(array $data, ?int $id = null, array $roleIds = []): User;

    /**
     * Remove um usuário.
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool;

    /**
     * Ativa ou inativa um usuário.
     *
     * @param int $id
     * @param bool $active
     * @return bool
     */
    public function setUserStatus(int $id, bool $active): bool;

    /**
     * Remove um perfil específico de um usuário.
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function removeUserRole(int $userId, int $roleId): bool;

    /**
     * Salva ou atualiza um perfil e suas permissões.
     *
     * @param array $data
     * @param int|null $id
     * @param array $permissionIds
     * @return Role
     */
    public function storeRole(array $data, ?int $id = null, array $permissionIds = []): Role;
}
