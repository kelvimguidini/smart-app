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

// Rotas públicas do Orçamento (acesso externo via link)
Route::get('budget', [\App\Http\Controllers\Api\BudgetApiController::class, 'show']);
Route::post('budget-save', [\App\Http\Controllers\Api\BudgetApiController::class, 'store']);
Route::post('budget-prove', [\App\Http\Controllers\Api\BudgetApiController::class, 'prove']);

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
    Route::delete('roles/permission', [RoleApiController::class, 'removePermission']);
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

    // Customer Requesters CRUD
    Route::get('customer-requesters', [\App\Http\Controllers\Api\CustomerRequesterApiController::class, 'index']);
    Route::post('customer-requesters', [\App\Http\Controllers\Api\CustomerRequesterApiController::class, 'store']);
    Route::delete('customer-requesters/{id}', [\App\Http\Controllers\Api\CustomerRequesterApiController::class, 'destroy']);
    Route::put('customer-requesters/activate/{id}', [\App\Http\Controllers\Api\CustomerRequesterApiController::class, 'activateItem']);
    Route::put('customer-requesters/deactivate/{id}', [\App\Http\Controllers\Api\CustomerRequesterApiController::class, 'deactivateItem']);

    // Customer Sectors CRUD
    Route::get('customer-sectors', [\App\Http\Controllers\Api\CustomerSectorApiController::class, 'index']);
    Route::post('customer-sectors', [\App\Http\Controllers\Api\CustomerSectorApiController::class, 'store']);
    Route::delete('customer-sectors/{id}', [\App\Http\Controllers\Api\CustomerSectorApiController::class, 'destroy']);
    Route::put('customer-sectors/activate/{id}', [\App\Http\Controllers\Api\CustomerSectorApiController::class, 'activateItem']);
    Route::put('customer-sectors/deactivate/{id}', [\App\Http\Controllers\Api\CustomerSectorApiController::class, 'deactivateItem']);

    // Customer Cost Centers CRUD
    Route::get('customer-cost-centers', [\App\Http\Controllers\Api\CustomerCostCenterApiController::class, 'index']);
    Route::post('customer-cost-centers', [\App\Http\Controllers\Api\CustomerCostCenterApiController::class, 'store']);
    Route::delete('customer-cost-centers/{id}', [\App\Http\Controllers\Api\CustomerCostCenterApiController::class, 'destroy']);
    Route::put('customer-cost-centers/activate/{id}', [\App\Http\Controllers\Api\CustomerCostCenterApiController::class, 'activateItem']);
    Route::put('customer-cost-centers/deactivate/{id}', [\App\Http\Controllers\Api\CustomerCostCenterApiController::class, 'deactivateItem']);

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

    // Rotas de Marcas de Veículo (Brand)
    Route::get('brands', [\App\Http\Controllers\Api\BrandApiController::class, 'index']);
    Route::post('brands', [\App\Http\Controllers\Api\BrandApiController::class, 'store']);
    Route::delete('brands/{id}', [\App\Http\Controllers\Api\BrandApiController::class, 'destroy']);
    Route::put('brands/{id}/activate', [\App\Http\Controllers\Api\BrandApiController::class, 'activateItem']);
    Route::put('brands/{id}/deactivate', [\App\Http\Controllers\Api\BrandApiController::class, 'deactivateItem']);

    // Rotas de Modelos de Veículo (CarModel)
    Route::get('car-models', [\App\Http\Controllers\Api\CarModelApiController::class, 'index']);
    Route::post('car-models', [\App\Http\Controllers\Api\CarModelApiController::class, 'store']);
    Route::delete('car-models/{id}', [\App\Http\Controllers\Api\CarModelApiController::class, 'destroy']);
    Route::put('car-models/{id}/activate', [\App\Http\Controllers\Api\CarModelApiController::class, 'activateItem']);
    Route::put('car-models/{id}/deactivate', [\App\Http\Controllers\Api\CarModelApiController::class, 'deactivateItem']);

    // Rotas de Tipos de Veículo (Vehicle)
    Route::get('vehicles', [\App\Http\Controllers\Api\VehicleApiController::class, 'index']);
    Route::post('vehicles', [\App\Http\Controllers\Api\VehicleApiController::class, 'store']);
    Route::delete('vehicles/{id}', [\App\Http\Controllers\Api\VehicleApiController::class, 'destroy']);
    Route::put('vehicles/{id}/activate', [\App\Http\Controllers\Api\VehicleApiController::class, 'activateItem']);
    Route::put('vehicles/{id}/deactivate', [\App\Http\Controllers\Api\VehicleApiController::class, 'deactivateItem']);

    // Rotas de Serviços de Transporte (TransportService)
    Route::get('transport-services', [\App\Http\Controllers\Api\TransportServiceApiController::class, 'index']);
    Route::post('transport-services', [\App\Http\Controllers\Api\TransportServiceApiController::class, 'store']);
    Route::delete('transport-services/{id}', [\App\Http\Controllers\Api\TransportServiceApiController::class, 'destroy']);
    Route::put('transport-services/{id}/activate', [\App\Http\Controllers\Api\TransportServiceApiController::class, 'activateItem']);
    Route::put('transport-services/{id}/deactivate', [\App\Http\Controllers\Api\TransportServiceApiController::class, 'deactivateItem']);

    // Rotas de Brokers de Transporte (BrokerTransport)
    Route::get('broker-transports', [\App\Http\Controllers\Api\BrokerTransportApiController::class, 'index']);
    Route::post('broker-transports', [\App\Http\Controllers\Api\BrokerTransportApiController::class, 'store']);
    Route::delete('broker-transports/{id}', [\App\Http\Controllers\Api\BrokerTransportApiController::class, 'destroy']);
    Route::put('broker-transports/{id}/activate', [\App\Http\Controllers\Api\BrokerTransportApiController::class, 'activateItem']);
    Route::put('broker-transports/{id}/deactivate', [\App\Http\Controllers\Api\BrokerTransportApiController::class, 'deactivateItem']);

    // Rotas de Fornecedores de Transporte (ProviderTransport)
    Route::get('provider-transports', [\App\Http\Controllers\Api\ProviderTransportApiController::class, 'index']);
    Route::post('provider-transports', [\App\Http\Controllers\Api\ProviderTransportApiController::class, 'store']);
    Route::delete('provider-transports/{id}', [\App\Http\Controllers\Api\ProviderTransportApiController::class, 'destroy']);
    Route::put('provider-transports/{id}/activate', [\App\Http\Controllers\Api\ProviderTransportApiController::class, 'activateItem']);
    Route::put('provider-transports/{id}/deactivate', [\App\Http\Controllers\Api\ProviderTransportApiController::class, 'deactivateItem']);

    // Rotas de Eventos (Angular SPA)
    Route::get('events/list', [\App\Http\Controllers\Api\EventApiController::class, 'listEvents']);
    Route::get('events/{id}/edit-data', [\App\Http\Controllers\Api\EventApiController::class, 'getEditData']);
    Route::post('events', [\App\Http\Controllers\Api\EventApiController::class, 'store']);
    Route::delete('events/{id}', [\App\Http\Controllers\Api\EventApiController::class, 'destroy']);
    Route::post('events/save-exchange-rate', [\App\Http\Controllers\Api\EventApiController::class, 'saveExchangeRate']);
    Route::post('events/save-vl-faturamento', [\App\Http\Controllers\Api\EventApiController::class, 'saveValorFaturamento']);
    Route::get('events/history/{event_id}', [\App\Http\Controllers\Auth\ProposalHistoryController::class, 'getHistory']);
    Route::post('events/history/restore', [\App\Http\Controllers\Auth\ProposalHistoryController::class, 'restore']);

    // Rotas de Vinculação de Fornecedores de Evento e Opções
    // 1. Hotel
    Route::post('event-hotels', [\App\Http\Controllers\Api\EventHotelApiController::class, 'store']);
    Route::delete('event-hotels/{id}', [\App\Http\Controllers\Api\EventHotelApiController::class, 'destroy']);
    Route::post('event-hotels/opts', [\App\Http\Controllers\Api\EventHotelApiController::class, 'storeOpt']);
    Route::delete('event-hotels/opts/{id}', [\App\Http\Controllers\Api\EventHotelApiController::class, 'destroyOpt']);

    // 2. A&B
    Route::post('event-abs', [\App\Http\Controllers\Api\EventABApiController::class, 'store']);
    Route::delete('event-abs/{id}', [\App\Http\Controllers\Api\EventABApiController::class, 'destroy']);
    Route::post('event-abs/opts', [\App\Http\Controllers\Api\EventABApiController::class, 'storeOpt']);
    Route::delete('event-abs/opts/{id}', [\App\Http\Controllers\Api\EventABApiController::class, 'destroyOpt']);

    // 3. Salão
    Route::post('event-halls', [\App\Http\Controllers\Api\EventHallApiController::class, 'store']);
    Route::delete('event-halls/{id}', [\App\Http\Controllers\Api\EventHallApiController::class, 'destroy']);
    Route::post('event-halls/opts', [\App\Http\Controllers\Api\EventHallApiController::class, 'storeOpt']);
    Route::delete('event-halls/opts/{id}', [\App\Http\Controllers\Api\EventHallApiController::class, 'destroyOpt']);

    // 4. Adicional
    Route::post('event-adds', [\App\Http\Controllers\Api\EventAddApiController::class, 'store']);
    Route::delete('event-adds/{id}', [\App\Http\Controllers\Api\EventAddApiController::class, 'destroy']);
    Route::post('event-adds/opts', [\App\Http\Controllers\Api\EventAddApiController::class, 'storeOpt']);
    Route::delete('event-adds/opts/{id}', [\App\Http\Controllers\Api\EventAddApiController::class, 'destroyOpt']);

    // 5. Transporte
    Route::post('event-transports', [\App\Http\Controllers\Api\EventTransportApiController::class, 'store']);
    Route::delete('event-transports/{id}', [\App\Http\Controllers\Api\EventTransportApiController::class, 'destroy']);
    Route::post('event-transports/opts', [\App\Http\Controllers\Api\EventTransportApiController::class, 'storeOpt']);
    Route::delete('event-transports/opts/{id}', [\App\Http\Controllers\Api\EventTransportApiController::class, 'destroyOpt']);

});


