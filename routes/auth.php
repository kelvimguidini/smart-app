<?php

use App\Http\Controllers\Auth\ABController;
use App\Http\Controllers\Auth\AddController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\BrandController;
use App\Http\Controllers\Auth\BrokerController;
use App\Http\Controllers\Auth\BrokerTransportController;
use App\Http\Controllers\Auth\BudgetController;
use App\Http\Controllers\Auth\CarModelController;
use App\Http\Controllers\Auth\CategoryController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\CustomerController;
use App\Http\Controllers\Auth\CrdController;
use App\Http\Controllers\Auth\CurrencyController;
use App\Http\Controllers\Auth\EventController;
use App\Http\Controllers\Auth\FrequencyController;
use App\Http\Controllers\Auth\HallController;
use App\Http\Controllers\Auth\HomeController;
use App\Http\Controllers\Auth\HotelController;
use App\Http\Controllers\Auth\LocalController;
use App\Http\Controllers\Auth\MeasureController;
use App\Http\Controllers\Auth\ProfileUserController;
use App\Http\Controllers\Auth\ProviderController;
use App\Http\Controllers\Auth\ProviderServicesController;
use App\Http\Controllers\Auth\ProviderTransportController;
use App\Http\Controllers\Auth\PurposeController;
use App\Http\Controllers\Auth\PurposeHallController;
use App\Http\Controllers\Auth\RegimeController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\ServiceAddController;
use App\Http\Controllers\Auth\ServiceController;
use App\Http\Controllers\Auth\ServiceHallController;
use App\Http\Controllers\Auth\ServiceTypeController;
use App\Http\Controllers\Auth\TransportController;
use App\Http\Controllers\Auth\TransportServiceController;
use App\Http\Controllers\Auth\VehicleController;
use App\Http\Controllers\Auth\StatusHistoryController;
use App\Http\Controllers\Auth\CityController;
use App\Http\Controllers\Auth\ProposalHistoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;


Route::middleware(['auth', 'cors'])->group(function () {

    Route::get('/', function (Request $request) {
        $path = base_path('public/angular.html');
        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
        }
        return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
    })->name('dashboard');

    Route::get('dashboard', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    });

    Route::get('roles', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('roles');

    Route::get('apto', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('apto');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });


    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    //USER
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('profile', [ProfileUserController::class, 'create'])
        ->name('profile');

    Route::post('profile', [ProfileUserController::class, 'store'])
        ->name('profile');

    Route::delete('user-delete', [RegisteredUserController::class, 'delete'])->name('user-delete');

    //ROLE
    Route::delete('role-remove', [RegisteredUserController::class, 'roleRemove'])->name('role-remove');

    Route::get('role', [RoleController::class, 'create'])
        ->name('role');

    Route::post('role-save', [RoleController::class, 'store'])
        ->name('role-save');

    Route::delete('role-delete', [RoleController::class, 'delete'])
        ->name('role-delete');


    Route::delete('permission-remove', [RoleController::class, 'permissionRemove'])
        ->name('permission-remove');

    //TABELS AUXILIARES
    Route::get('customer', [CustomerController::class, 'create'])
        ->name('customer');

    Route::post('customer-save', [CustomerController::class, 'store'])
        ->name('customer-save');

    Route::delete('customer-delete', [CustomerController::class, 'delete'])
        ->name('customer-delete');

    Route::get('crd', [CrdController::class, 'create'])
        ->name('crd');

    Route::post('crd-save', [CrdController::class, 'store'])
        ->name('crd-save');

    Route::delete('crd-delete', [CrdController::class, 'delete'])
        ->name('crd-delete');



    Route::get('city', [CityController::class, 'create'])
        ->name('city');

    Route::post('city-save', [CityController::class, 'store'])
        ->name('city-save');

    Route::delete('city-delete', [CityController::class, 'delete'])
        ->name('city-delete');


    Route::get('cities', [CityController::class, 'searchCities'])
        ->name('cities');



    // Apto routes migrated to api.php (Angular)

    Route::get('category', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('category');

    // Category save and delete routes migrated to api.php (Angular)


    Route::get('regime', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('regime');

    // Regime save and delete routes migrated to api.php (Angular)



    Route::get('purpose', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('purpose');

    // Purpose save and delete routes migrated to api.php (Angular)


    Route::get('broker', [BrokerController::class, 'create'])
        ->name('broker');

    Route::post('broker-save', [BrokerController::class, 'store'])
        ->name('broker-save');

    Route::delete('broker-delete', [BrokerController::class, 'delete'])
        ->name('broker-delete');


    Route::get('broker-trans', [BrokerTransportController::class, 'create'])
        ->name('broker-trans');

    Route::post('broker-trans-save', [BrokerTransportController::class, 'store'])
        ->name('broker-trans-save');

    Route::delete('broker-trans-delete', [BrokerTransportController::class, 'delete'])
        ->name('broker-trans-delete');


    Route::get('vehicle', [VehicleController::class, 'create'])
        ->name('vehicle');

    Route::post('vehicle-save', [VehicleController::class, 'store'])
        ->name('vehicle-save');

    Route::delete('vehicle-delete', [VehicleController::class, 'delete'])
        ->name('vehicle-delete');


    Route::get('brand', [BrandController::class, 'create'])
        ->name('brand');

    Route::post('brand-save', [BrandController::class, 'store'])
        ->name('brand-save');

    Route::delete('brand-delete', [BrandController::class, 'delete'])
        ->name('brand-delete');


    Route::get('car-model', [CarModelController::class, 'create'])
        ->name('car-model');

    Route::post('car-model-save', [CarModelController::class, 'store'])
        ->name('car-model-save');

    Route::delete('car-model-delete', [CarModelController::class, 'delete'])
        ->name('car-model-delete');


    Route::get('transport-service', [TransportServiceController::class, 'create'])
        ->name('transport-service');

    Route::post('transport-service-save', [TransportServiceController::class, 'store'])
        ->name('transport-service-save');

    Route::delete('transport-service-delete', [TransportServiceController::class, 'delete'])
        ->name('transport-service-delete');



    Route::get('local', [LocalController::class, 'create'])
        ->name('local');

    Route::post('local-save', [LocalController::class, 'store'])
        ->name('local-save');

    Route::delete('local-delete', [LocalController::class, 'delete'])
        ->name('local-delete');


    Route::get('service', [ServiceController::class, 'create'])
        ->name('service');

    Route::post('service-save', [ServiceController::class, 'store'])
        ->name('service-save');

    Route::delete('service-delete', [ServiceController::class, 'delete'])
        ->name('service-delete');



    Route::get('service-hall', [ServiceHallController::class, 'create'])
        ->name('service-hall');

    Route::post('service-hall-save', [ServiceHallController::class, 'store'])
        ->name('service-hall-save');

    Route::delete('service-hall-delete', [ServiceHallController::class, 'delete'])
        ->name('service-hall-delete');


    Route::get('service-add', [ServiceAddController::class, 'create'])
        ->name('service-add');

    Route::post('service-add-save', [ServiceAddController::class, 'store'])
        ->name('service-add-save');

    Route::delete('service-add-delete', [ServiceAddController::class, 'delete'])
        ->name('service-add-delete');




    Route::get('purpose-hall', [PurposeHallController::class, 'create'])
        ->name('purpose-hall');

    Route::post('purpose-hall-save', [PurposeHallController::class, 'store'])
        ->name('purpose-hall-save');

    Route::delete('purpose-hall-delete', [PurposeHallController::class, 'delete'])
        ->name('purpose-hall-delete');



    Route::get('service-type', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('service-type');

    // Service Type save and delete routes migrated to api.php (Angular)



    Route::get('currency', [CurrencyController::class, 'create'])
        ->name('currency');

    Route::post('currency-save', [CurrencyController::class, 'store'])
        ->name('currency-save');

    Route::delete('currency-delete', [CurrencyController::class, 'delete'])
        ->name('currency-delete');


    Route::get('measure', [MeasureController::class, 'create'])
        ->name('measure');

    Route::post('measure-save', [MeasureController::class, 'store'])
        ->name('measure-save');

    Route::delete('measure-delete', [MeasureController::class, 'delete'])
        ->name('measure-delete');


    Route::get('frequency', [FrequencyController::class, 'create'])
        ->name('frequency');

    Route::post('frequency-save', [FrequencyController::class, 'store'])
        ->name('frequency-save');

    Route::delete('frequency-delete', [FrequencyController::class, 'delete'])
        ->name('frequency-delete');


    //Eventos
    Route::get('event-list/{page?}', [EventController::class, 'list'])
        ->name('event-list');

    Route::post('event-list/{page?}', [EventController::class, 'list'])
        ->name('event-list.filter');

    Route::get('event', [EventController::class, 'create'])
        ->name('event-create');

    Route::get('event/{id}/{tab?}/{ehotel?}', [EventController::class, 'create'])
        ->name('event-edit');

    Route::post('event-save', [EventController::class, 'store'])
        ->name('event-save');


    Route::delete('event-delete', [EventController::class, 'delete'])
        ->name('event-delete');


    Route::get('invoice/{download}/{provider_id}/{event_id}/{type}', [ProviderController::class, 'invoicingPdf'])
        ->name('invoice');


    Route::post('invoice-email', [ProviderController::class, 'invoicingPdf'])
        ->name('invoice-email');

    Route::post('event/save-exchange-rate', [EventController::class, 'saveExchangeRate'])
        ->name('event-save-exchange-rate');


    Route::post('event/save-vl-faturamento', [EventController::class, 'saveValorFaturamento'])
        ->name('event-save-vl-faturamento');


    //HOTEL
    Route::get('hotel', [ProviderController::class, 'create'])
        ->name('hotel');

    Route::post('hotel-save', [ProviderController::class, 'store'])
        ->name('hotel-save');

    Route::delete('hotel-delete', [ProviderController::class, 'delete'])
        ->name('hotel-delete');

    Route::post('hotel-event-save', [ProviderController::class, 'storeEventProvider'])
        ->name('hotel-event-save');

    Route::delete('event-hotel-delete', [HotelController::class, 'eventHotelDelete'])
        ->name('event-hotel-delete');

    Route::post('hotel-opt-save', [HotelController::class, 'storeHotelOpt'])
        ->name('hotel-opt-save');

    Route::delete('opt-delete', [HotelController::class, 'optDelete'])
        ->name('opt-delete');

    //AB
    Route::post('ab-opt-save', [ABController::class, 'storeOpt'])
        ->name('ab-opt-save');

    Route::delete('opt-ab-delete', [ABController::class, 'optDelete'])
        ->name('opt-ab-delete');

    Route::delete('event-ab-delete', [ABController::class, 'eventABDelete'])
        ->name('event-ab-delete');


    //HALL
    Route::post('hall-opt-save', [HallController::class, 'storeOpt'])
        ->name('hall-opt-save');

    Route::delete('opt-hall-delete', [HallController::class, 'optDelete'])
        ->name('opt-hall-delete');

    Route::delete('event-hall-delete', [HallController::class, 'eventHallDelete'])
        ->name('event-hall-delete');


    //ADICIONAL
    Route::post('add-opt-save', [AddController::class, 'storeOpt'])
        ->name('add-opt-save');

    Route::delete('opt-add-delete', [AddController::class, 'optDelete'])
        ->name('opt-add-delete');

    Route::delete('event-add-delete', [AddController::class, 'eventAddDelete'])
        ->name('event-add-delete');

    Route::get('provider-service', [ProviderServicesController::class, 'create'])
        ->name('provider-service');

    Route::post('provider-service-save', [ProviderServicesController::class, 'store'])
        ->name('provider-service-save');

    Route::delete('provider-service-delete', [ProviderServicesController::class, 'delete'])
        ->name('provider-service-delete');

    Route::post('provider-service-event-save', [ProviderServicesController::class, 'storeEventProvider'])
        ->name('provider-service-event-save');

    //TRANSPORTE
    Route::post('transport-opt-save', [TransportController::class, 'storeOpt'])
        ->name('transport-opt-save');

    Route::delete('opt-transport-delete', [TransportController::class, 'optDelete'])
        ->name('opt-transport-delete');

    Route::delete('event-transport-delete', [TransportController::class, 'eventTransportDelete'])
        ->name('event-transport-delete');


    Route::get('provider-transport', [ProviderTransportController::class, 'create'])
        ->name('provider-transport');

    Route::post('provider-transport-save', [ProviderTransportController::class, 'store'])
        ->name('provider-transport-save');

    Route::delete('provider-transport-delete', [ProviderTransportController::class, 'delete'])
        ->name('provider-transport-delete');

    Route::post('provider-transport-event-save', [ProviderTransportController::class, 'storeEventProvider'])
        ->name('provider-transport-event-save');

    //ORÇAMENTO
    Route::post('budget-prove', [BudgetController::class, 'prove'])
        ->name('budget-prove');

    Route::get('create-link/{download}/{provider_id}/{event_id}/{link}/{type}', [BudgetController::class, 'createLink'])
        ->name('create-link');

    Route::post('create-link-email', [BudgetController::class, 'createLink'])
        ->name('create-link-email');

    Route::get('proposal-hotel/{download}/{provider_id}/{event_id}/{type}', [ProviderController::class, 'proposalPdf'])
        ->name('proposal-hotel');


    Route::post('proposal-hotel-email', [ProviderController::class, 'proposalPdf'])
        ->name('proposal-hotel-email');

    Route::get('proposal-hotel-without-values/{download}/{provider_id}/{event_id}/{type}', [ProviderController::class, 'proposalPdfWithoutValues'])
        ->name('proposal-hotel-without-values');

    Route::post('proposal-hotel-email-without-values', [ProviderController::class, 'proposalPdfWithoutValues'])
        ->name('proposal-hotel-email-without-values');
    //FIM HOTEL

    //dashboard
    Route::get('/dashboard-data', [HomeController::class, 'fetchDashboardData'])
        ->name('dashboard-data');


    //FIM dashboard

    //status
    Route::get('status-history/{table}/{table_id}', [StatusHistoryController::class, 'listHistory'])
        ->name('status-history');


    Route::post('event-status-save', [StatusHistoryController::class, 'statusStore'])
        ->name('event-status-save');


    Route::post('send-mail', [StatusHistoryController::class, 'sendMail'])
        ->name('event-status-send-email');

    //FIM status

    //ativar - desativar
    Route::put('/brands/activate/{id}', [BrandController::class, 'activateM'])->name('brand-activate');
    Route::put('/brands/deactivate/{id}', [BrandController::class, 'deactivateM'])->name('brand-deactivate');

    // Categories activate/deactivate routes migrated to api.php (Angular)

    // Apto activate/deactivate routes migrated to api.php (Angular)

    Route::put('/vehicles/activate/{id}', [VehicleController::class, 'activateM'])->name('vehicles-activate');
    Route::put('/vehicles/deactivate/{id}', [VehicleController::class, 'deactivateM'])->name('vehicles-deactivate');

    Route::put('/transport_services/activate/{id}', [TransportServiceController::class, 'activateM'])->name('transport_services-activate');
    Route::put('/transport_services/deactivate/{id}', [TransportServiceController::class, 'deactivateM'])->name('transport_services-deactivate');

    // Service Type activate/deactivate routes migrated to api.php (Angular)

    Route::put('/service_halls/activate/{id}', [ServiceHallController::class, 'activateM'])->name('service_halls-activate');
    Route::put('/service_halls/deactivate/{id}', [ServiceHallController::class, 'deactivateM'])->name('service_halls-deactivate');

    Route::put('/service_adds/activate/{id}', [ServiceAddController::class, 'activateM'])->name('service_adds-activate');
    Route::put('/service_adds/deactivate/{id}', [ServiceAddController::class, 'deactivateM'])->name('service_adds-deactivate');

    Route::put('/services/activate/{id}', [ServiceController::class, 'activateM'])->name('services-activate');
    Route::put('/services/deactivate/{id}', [ServiceController::class, 'deactivateM'])->name('services-deactivate');

    // Regimes activate/deactivate routes migrated to api.php (Angular)

    // Purposes activate/deactivate routes migrated to api.php (Angular)

    Route::put('/purpose_halls/activate/{id}', [PurposeHallController::class, 'activateM'])->name('purpose_halls-activate');
    Route::put('/purpose_halls/deactivate/{id}', [PurposeHallController::class, 'deactivateM'])->name('purpose_halls-deactivate');

    Route::put('/provider_transports/activate/{id}', [ProviderTransportController::class, 'activateM'])->name('provider_transports-activate');
    Route::put('/provider_transports/deactivate/{id}', [ProviderTransportController::class, 'deactivateM'])->name('provider_transports-deactivate');

    Route::put('/provider_services/activate/{id}', [ProviderServicesController::class, 'activateM'])->name('provider_services-activate');
    Route::put('/provider_services/deactivate/{id}', [ProviderServicesController::class, 'deactivateM'])->name('provider_services-deactivate');

    Route::put('/providers/activate/{id}', [ProviderController::class, 'activateM'])->name('providers-activate');
    Route::put('/providers/deactivate/{id}', [ProviderController::class, 'deactivateM'])->name('providers-deactivate');

    Route::put('/measures/activate/{id}', [MeasureController::class, 'activateM'])->name('measures-activate');
    Route::put('/measures/deactivate/{id}', [MeasureController::class, 'deactivateM'])->name('measures-deactivate');

    Route::put('/locals/activate/{id}', [LocalController::class, 'activateM'])->name('locals-activate');
    Route::put('/locals/deactivate/{id}', [LocalController::class, 'deactivateM'])->name('locals-deactivate');

    Route::put('/frequencies/activate/{id}', [FrequencyController::class, 'activateM'])->name('frequencies-activate');
    Route::put('/frequencies/deactivate/{id}', [FrequencyController::class, 'deactivateM'])->name('frequencies-deactivate');

    Route::put('/customers/activate/{id}', [CustomerController::class, 'activateM'])->name('customers-activate');
    Route::put('/customers/deactivate/{id}', [CustomerController::class, 'deactivateM'])->name('customers-deactivate');

    Route::put('/currencies/activate/{id}', [CurrencyController::class, 'activateM'])->name('currencies-activate');
    Route::put('/currencies/deactivate/{id}', [CurrencyController::class, 'deactivateM'])->name('currencies-deactivate');

    Route::put('/crds/activate/{id}', [CrdController::class, 'activateM'])->name('crds-activate');
    Route::put('/crds/deactivate/{id}', [CrdController::class, 'deactivateM'])->name('crds-deactivate');

    Route::put('/cities/activate/{id}', [CityController::class, 'activateM'])->name('cities-activate');
    Route::put('/cities/deactivate/{id}', [CityController::class, 'deactivateM'])->name('cities-deactivate');

    Route::put('/car_models/activate/{id}', [CarModelController::class, 'activateM'])->name('car_models-activate');
    Route::put('/car_models/deactivate/{id}', [CarModelController::class, 'deactivateM'])->name('car_models-deactivate');

    Route::put('/broker_transports/activate/{id}', [BrokerTransportController::class, 'activateM'])->name('broker_transports-activate');
    Route::put('/broker_transports/deactivate/{id}', [BrokerTransportController::class, 'deactivateM'])->name('broker_transports-deactivate');

    Route::put('/brokers/activate/{id}', [BrokerController::class, 'activateM'])->name('brokers-activate');
    Route::put('/brokers/deactivate/{id}', [BrokerController::class, 'deactivateM'])->name('brokers-deactivate');

    Route::put('/register/activate/{id}', [RegisteredUserController::class, 'activateM'])->name('register-activate');
    Route::put('/register/deactivate/{id}', [RegisteredUserController::class, 'deactivateM'])->name('register-deactivate');

    //FIM ativar - desativar

    Route::post('/update-provider-order', [ProviderController::class, 'updateOrder'])->name('update-provider-order');

    // routes/web.php
    Route::get('history/{event_id}', [ProposalHistoryController::class, 'getHistory'])
        ->name('history-get');


    Route::post('history/restore', [ProposalHistoryController::class, 'restore'])
        ->name('history-restore');
});
