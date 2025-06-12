<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
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
     *         description="Unauthorized"
     *     )
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user || !$user->is_api_user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        if (!$token = auth('api')->attempt($credentials)) {
            return $this->errorResponse('Unauthorized', 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
