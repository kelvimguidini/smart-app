<?php

namespace App\Domains\Auth\Repositories;

use App\Models\User;

interface AuthApiRepositoryInterface
{
    /**
     * Find a user by email, specifically checking if they are allowed API access.
     *
     * @param string $email
     * @return User|null
     */
    public function findApiUserByEmail(string $email): ?User;
}
