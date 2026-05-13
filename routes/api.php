<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\RoleApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/**
 * Rotas para Terceiros (API Externa)
 */
Route::post('login', [AuthController::class, 'login']);
Route::get('events', [EventApiController::class, 'index'])->middleware('auth:api');

/**
 * Rotas internas para o SPA Angular
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $user->permissions = $user->getPermissions();
        return $user;
    });

    // Rotas de Apartamentos (Apto)
    Route::get('aptos', [\App\Http\Controllers\Api\AptoApiController::class, 'index']);
    Route::post('aptos', [\App\Http\Controllers\Api\AptoApiController::class, 'store']);
    Route::delete('aptos/{id}', [\App\Http\Controllers\Api\AptoApiController::class, 'destroy']);
    Route::put('aptos/{id}/activate', [\App\Http\Controllers\Api\AptoApiController::class, 'activateItem']);
    Route::put('aptos/{id}/deactivate', [\App\Http\Controllers\Api\AptoApiController::class, 'deactivateItem']);

    // Rotas de Regimes
    Route::get('regimes', [\App\Http\Controllers\Api\RegimeApiController::class, 'index']);
    Route::post('regimes', [\App\Http\Controllers\Api\RegimeApiController::class, 'store']);
    Route::delete('regimes/{id}', [\App\Http\Controllers\Api\RegimeApiController::class, 'destroy']);
    Route::put('regimes/{id}/activate', [\App\Http\Controllers\Api\RegimeApiController::class, 'activateItem']);
    Route::put('regimes/{id}/deactivate', [\App\Http\Controllers\Api\RegimeApiController::class, 'deactivateItem']);

    // Rotas de Categorias
    Route::get('categories', [\App\Http\Controllers\Api\CategoryApiController::class, 'index']);
    Route::post('categories', [\App\Http\Controllers\Api\CategoryApiController::class, 'store']);
    Route::delete('categories/{id}', [\App\Http\Controllers\Api\CategoryApiController::class, 'destroy']);
    Route::put('categories/{id}/activate', [\App\Http\Controllers\Api\CategoryApiController::class, 'activateItem']);
    Route::put('categories/{id}/deactivate', [\App\Http\Controllers\Api\CategoryApiController::class, 'deactivateItem']);
});
