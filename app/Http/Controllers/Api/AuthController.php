<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Domains\Auth\Services\AuthApiServiceInterface;

class AuthController extends BaseApiController
{
    protected AuthApiServiceInterface $authApiService;

    public function __construct(AuthApiServiceInterface $authApiService)
    {
        $this->authApiService = $authApiService;
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Autenticação de usuário da API",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="apiuser@teste.com"),
     *             @OA\Property(property="password", type="string", example="senha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token JWT retornado",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string"),
     *             @OA\Property(property="expires_in", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $tokenData = $this->authApiService->authenticate($credentials);

        if (!$tokenData) {
            return $this->errorResponse('Unauthorized', 401);
        }

        return response()->json($tokenData);
    }
}
