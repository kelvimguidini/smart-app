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

Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('events', [EventApiController::class, 'index']);

    // // Rotas de Grupos de Acesso (Roles)
    // Route::get('roles', [RoleApiController::class, 'index']);
    // Route::post('roles', [RoleApiController::class, 'store']);
    // Route::delete('roles', [RoleApiController::class, 'delete']);
    // Route::delete('roles/permission', [RoleApiController::class, 'removePermission']);
});

Route::middleware('auth:sanctum')->get('/user', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    $user->permissions = $user->getPermissions();
    return $user;
});
