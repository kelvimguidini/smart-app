<?php

namespace App\Domains\Auth\Repositories;

use App\Models\User;

class EloquentAuthApiRepository implements AuthApiRepositoryInterface
{
    /**
     * Find a user by email, specifically checking if they are allowed API access.
     *
     * @param string $email
     * @return User|null
     */
    public function findApiUserByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->where('is_api_user', 1)
            ->first();
    }
}
