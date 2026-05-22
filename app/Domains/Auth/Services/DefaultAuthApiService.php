<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Repositories\AuthApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class DefaultAuthApiService implements AuthApiServiceInterface
{
    protected AuthApiRepositoryInterface $authApiRepository;

    public function __construct(AuthApiRepositoryInterface $authApiRepository)
    {
        $this->authApiRepository = $authApiRepository;
    }

    /**
     * Authenticate an API user with the given credentials.
     *
     * @param array $credentials
     * @return array|null
     */
    public function authenticate(array $credentials): ?array
    {
        if (!isset($credentials['email']) || !isset($credentials['password'])) {
            return null;
        }

        $user = $this->authApiRepository->findApiUserByEmail($credentials['email']);

        if (!$user) {
            return null;
        }

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return null;
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ];
    }
}
