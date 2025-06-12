<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="SmartApp API",
 *     description="Documentação da API do SmartApp",
 *     @OA\Contact(
 *         email="suporte@seudominio.com"
 *     )
 * )
 */
class BaseApiController extends Controller
{
    protected function errorResponse($message = 'Erro', $status = 400, $errors = [])
    {
        $response = ['message' => $message];
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $status);
    }
}
