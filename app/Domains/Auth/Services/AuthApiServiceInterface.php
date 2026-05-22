<?php

namespace App\Domains\Auth\Services;

interface AuthApiServiceInterface
{
    /**
     * Authenticate an API user with the given credentials.
     *
     * @param array $credentials
     * @return array|null Returns token array or null if authentication fails.
     */
    public function authenticate(array $credentials): ?array;
}
