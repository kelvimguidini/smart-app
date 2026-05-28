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

    // Rotas de Grupo de Acesso (Roles)
    Route::get('roles', [RoleApiController::class, 'index']);
    Route::post('roles', [RoleApiController::class, 'store']);
    Route::delete('roles', [RoleApiController::class, 'delete']);
    // Rotas de Usuários
    Route::get('users', [\App\Http\Controllers\Api\UserApiController::class, 'index']);
    Route::post('users', [\App\Http\Controllers\Api\UserApiController::class, 'store']);
    Route::delete('users/{id}', [\App\Http\Controllers\Api\UserApiController::class, 'destroy']);
    Route::put('users/{id}/activate', [\App\Http\Controllers\Api\UserApiController::class, 'activateItem']);
    Route::put('users/{id}/deactivate', [\App\Http\Controllers\Api\UserApiController::class, 'deactivateItem']);
    Route::delete('users/role', [\App\Http\Controllers\Api\UserApiController::class, 'roleRemove']);

    // Rotas de Perfil
    Route::get('profile', [\App\Http\Controllers\Api\ProfileApiController::class, 'show']);
    Route::post('profile', [\App\Http\Controllers\Api\ProfileApiController::class, 'store']);

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
    Route::put('/categories/deactivate/{id}', [\App\Http\Controllers\Api\CategoryApiController::class, 'deactivateItem']);

    // Currencies
    Route::get('/currencies', [\App\Http\Controllers\Api\CurrencyApiController::class, 'index']);
    Route::post('/currencies', [\App\Http\Controllers\Api\CurrencyApiController::class, 'store']);
    Route::delete('/currencies/{id}', [\App\Http\Controllers\Api\CurrencyApiController::class, 'destroy']);
    Route::put('/currencies/activate/{id}', [\App\Http\Controllers\Api\CurrencyApiController::class, 'activateItem']);
    Route::put('/currencies/deactivate/{id}', [\App\Http\Controllers\Api\CurrencyApiController::class, 'deactivateItem']);

    // Customers
    Route::get('/customers', [\App\Http\Controllers\Api\CustomerApiController::class, 'index']);
    Route::post('/customers', [\App\Http\Controllers\Api\CustomerApiController::class, 'store']);
    Route::delete('/customers/{id}', [\App\Http\Controllers\Api\CustomerApiController::class, 'destroy']);
    Route::put('/customers/activate/{id}', [\App\Http\Controllers\Api\CustomerApiController::class, 'activateItem']);
    Route::put('/customers/deactivate/{id}', [\App\Http\Controllers\Api\CustomerApiController::class, 'deactivateItem']);

    // CRDs
    Route::get('/crds', [\App\Http\Controllers\Api\CrdApiController::class, 'index']);
    Route::post('/crds', [\App\Http\Controllers\Api\CrdApiController::class, 'store']);
    Route::delete('/crds/{id}', [\App\Http\Controllers\Api\CrdApiController::class, 'destroy']);
    Route::put('/crds/activate/{id}', [\App\Http\Controllers\Api\CrdApiController::class, 'activateItem']);
    Route::put('/crds/deactivate/{id}', [\App\Http\Controllers\Api\CrdApiController::class, 'deactivateItem']);

    // Rotas de Propósitos (Purpose)
    Route::get('purposes', [\App\Http\Controllers\Api\PurposeApiController::class, 'index']);
    Route::post('purposes', [\App\Http\Controllers\Api\PurposeApiController::class, 'store']);
    Route::delete('purposes/{id}', [\App\Http\Controllers\Api\PurposeApiController::class, 'destroy']);
    Route::put('purposes/{id}/activate', [\App\Http\Controllers\Api\PurposeApiController::class, 'activateItem']);
    Route::put('purposes/{id}/deactivate', [\App\Http\Controllers\Api\PurposeApiController::class, 'deactivateItem']);

    // Rotas de Tipos de Serviço (ServiceType)
    Route::get('service-types', [\App\Http\Controllers\Api\ServiceTypeApiController::class, 'index']);
    Route::post('service-types', [\App\Http\Controllers\Api\ServiceTypeApiController::class, 'store']);
    Route::delete('service-types/{id}', [\App\Http\Controllers\Api\ServiceTypeApiController::class, 'destroy']);
    Route::put('service-types/{id}/activate', [\App\Http\Controllers\Api\ServiceTypeApiController::class, 'activateItem']);
    Route::put('service-types/{id}/deactivate', [\App\Http\Controllers\Api\ServiceTypeApiController::class, 'deactivateItem']);

    // Rotas de Serviços (Service)
    Route::get('services', [\App\Http\Controllers\Api\ServiceApiController::class, 'index']);
    Route::post('services', [\App\Http\Controllers\Api\ServiceApiController::class, 'store']);
    Route::delete('services/{id}', [\App\Http\Controllers\Api\ServiceApiController::class, 'destroy']);
    Route::put('services/{id}/activate', [\App\Http\Controllers\Api\ServiceApiController::class, 'activateItem']);
    Route::put('services/{id}/deactivate', [\App\Http\Controllers\Api\ServiceApiController::class, 'deactivateItem']);

    // Rotas de Locais (Local)
    Route::get('locals', [\App\Http\Controllers\Api\LocalApiController::class, 'index']);
    Route::post('locals', [\App\Http\Controllers\Api\LocalApiController::class, 'store']);
    Route::delete('locals/{id}', [\App\Http\Controllers\Api\LocalApiController::class, 'destroy']);
    Route::put('locals/{id}/activate', [\App\Http\Controllers\Api\LocalApiController::class, 'activateItem']);
    Route::put('locals/{id}/deactivate', [\App\Http\Controllers\Api\LocalApiController::class, 'deactivateItem']);

    // Rotas de Serviços do Salão (ServiceHall)
    Route::get('service-halls', [\App\Http\Controllers\Api\ServiceHallApiController::class, 'index']);
    Route::post('service-halls', [\App\Http\Controllers\Api\ServiceHallApiController::class, 'store']);
    Route::delete('service-halls/{id}', [\App\Http\Controllers\Api\ServiceHallApiController::class, 'destroy']);
    Route::put('service-halls/{id}/activate', [\App\Http\Controllers\Api\ServiceHallApiController::class, 'activateItem']);
    Route::put('service-halls/{id}/deactivate', [\App\Http\Controllers\Api\ServiceHallApiController::class, 'deactivateItem']);

    // Rotas de Propósito do Salão (PurposeHall)
    Route::get('purpose-halls', [\App\Http\Controllers\Api\PurposeHallApiController::class, 'index']);
    Route::post('purpose-halls', [\App\Http\Controllers\Api\PurposeHallApiController::class, 'store']);
    Route::delete('purpose-halls/{id}', [\App\Http\Controllers\Api\PurposeHallApiController::class, 'destroy']);
    Route::put('purpose-halls/{id}/activate', [\App\Http\Controllers\Api\PurposeHallApiController::class, 'activateItem']);
    Route::put('purpose-halls/{id}/deactivate', [\App\Http\Controllers\Api\PurposeHallApiController::class, 'deactivateItem']);

    // Rotas de Brokers (Brokers/Corretoras)
    Route::get('brokers', [\App\Http\Controllers\Api\BrokerApiController::class, 'index']);
    Route::post('brokers', [\App\Http\Controllers\Api\BrokerApiController::class, 'store']);
    Route::delete('brokers/{id}', [\App\Http\Controllers\Api\BrokerApiController::class, 'destroy']);
    Route::put('brokers/{id}/activate', [\App\Http\Controllers\Api\BrokerApiController::class, 'activateItem']);
    Route::put('brokers/{id}/deactivate', [\App\Http\Controllers\Api\BrokerApiController::class, 'deactivateItem']);

    // Rotas de Cidades
    Route::get('cities', [\App\Http\Controllers\Api\CityApiController::class, 'index']);
    Route::post('cities', [\App\Http\Controllers\Api\CityApiController::class, 'store']);
    Route::delete('cities/{id}', [\App\Http\Controllers\Api\CityApiController::class, 'destroy']);
    Route::put('cities/activate/{id}', [\App\Http\Controllers\Api\CityApiController::class, 'activateItem']);
    Route::put('cities/deactivate/{id}', [\App\Http\Controllers\Api\CityApiController::class, 'deactivateItem']);
    Route::get('cities/search', [\App\Http\Controllers\Api\CityApiController::class, 'search']);

    // Rotas de Hotéis (Fornecedores)
    Route::get('hotels', [\App\Http\Controllers\Api\ProviderApiController::class, 'index']);
    Route::post('hotels', [\App\Http\Controllers\Api\ProviderApiController::class, 'store']);
    Route::delete('hotels/{id}', [\App\Http\Controllers\Api\ProviderApiController::class, 'destroy']);
    Route::put('hotels/{id}/activate', [\App\Http\Controllers\Api\ProviderApiController::class, 'activateItem']);
    // Rotas de Medidas (Measure)
    Route::get('measures', [\App\Http\Controllers\Api\MeasureApiController::class, 'index']);
    Route::post('measures', [\App\Http\Controllers\Api\MeasureApiController::class, 'store']);
    Route::delete('measures/{id}', [\App\Http\Controllers\Api\MeasureApiController::class, 'destroy']);
    Route::put('measures/{id}/activate', [\App\Http\Controllers\Api\MeasureApiController::class, 'activateItem']);
    Route::put('measures/{id}/deactivate', [\App\Http\Controllers\Api\MeasureApiController::class, 'deactivateItem']);

    // Rotas de Frequências (Frequency)
    Route::get('frequencies', [\App\Http\Controllers\Api\FrequencyApiController::class, 'index']);
    Route::post('frequencies', [\App\Http\Controllers\Api\FrequencyApiController::class, 'store']);
    Route::delete('frequencies/{id}', [\App\Http\Controllers\Api\FrequencyApiController::class, 'destroy']);
    Route::put('frequencies/{id}/activate', [\App\Http\Controllers\Api\FrequencyApiController::class, 'activateItem']);
    Route::put('frequencies/{id}/deactivate', [\App\Http\Controllers\Api\FrequencyApiController::class, 'deactivateItem']);

    // Rotas de Serviços Adicionais (ServiceAdd)
    Route::get('service-adds', [\App\Http\Controllers\Api\ServiceAddApiController::class, 'index']);
    Route::post('service-adds', [\App\Http\Controllers\Api\ServiceAddApiController::class, 'store']);
    Route::delete('service-adds/{id}', [\App\Http\Controllers\Api\ServiceAddApiController::class, 'destroy']);
    Route::put('service-adds/{id}/activate', [\App\Http\Controllers\Api\ServiceAddApiController::class, 'activateItem']);
    Route::put('service-adds/{id}/deactivate', [\App\Http\Controllers\Api\ServiceAddApiController::class, 'deactivateItem']);

    // Rotas de Fornecedores de Serviço (ProviderService)
    Route::get('provider-services', [\App\Http\Controllers\Api\ProviderServiceApiController::class, 'index']);
    Route::post('provider-services', [\App\Http\Controllers\Api\ProviderServiceApiController::class, 'store']);
    Route::delete('provider-services/{id}', [\App\Http\Controllers\Api\ProviderServiceApiController::class, 'destroy']);
    Route::put('provider-services/{id}/activate', [\App\Http\Controllers\Api\ProviderServiceApiController::class, 'activateItem']);
    Route::put('provider-services/{id}/deactivate', [\App\Http\Controllers\Api\ProviderServiceApiController::class, 'deactivateItem']);

});
