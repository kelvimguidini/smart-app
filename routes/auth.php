<?php

use App\Http\Controllers\Auth\ABController;
use App\Http\Controllers\Auth\AddController;
use App\Http\Controllers\Auth\AptoController;
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
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EventController;
use App\Http\Controllers\Auth\FrequencyController;
use App\Http\Controllers\Auth\HallController;
use App\Http\Controllers\Auth\HomeController;
use App\Http\Controllers\Auth\HotelController;
use App\Http\Controllers\Auth\LocalController;
use App\Http\Controllers\Auth\MeasureController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
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
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\StatusHistoryController;
use App\Http\Controllers\Auth\CityController;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'cors'])->group(function () {

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');


    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');


    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['throttle:6,1'])
        ->name('verification.verify');

    Route::get('new-event/{params}', function ($params) {
        $paramsCript = Crypt::decryptString($params);
        $parameters = json_decode($paramsCript, true);

        return app()->make('App\Http\Controllers\HotelController')->mostrarPagina($parameters['event'], $parameters['provider']);
    });
});

Route::middleware(['cors'])->group(function () {

    Route::get('budget/{token}/{prove?}/{user?}', [BudgetController::class, 'budget'])
        ->name('budget');

    Route::post('budget-save', [BudgetController::class, 'store'])
        ->name('budget-save');
});

Route::middleware(['auth', 'cors'])->group(function () {

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



    Route::get('apto', [AptoController::class, 'create'])
        ->name('apto');

    Route::post('apto-save', [AptoController::class, 'store'])
        ->name('apto-save');

    Route::delete('apto-delete', [AptoController::class, 'delete'])
        ->name('apto-delete');


    Route::get('category', [CategoryController::class, 'create'])
        ->name('category');

    Route::post('category-save', [CategoryController::class, 'store'])
        ->name('category-save');

    Route::delete('category-delete', [CategoryController::class, 'delete'])
        ->name('category-delete');


    Route::get('regime', [RegimeController::class, 'create'])
        ->name('regime');

    Route::post('regime-save', [RegimeController::class, 'store'])
        ->name('regime-save');

    Route::delete('regime-delete', [RegimeController::class, 'delete'])
        ->name('regime-delete');



    Route::get('purpose', [PurposeController::class, 'create'])
        ->name('purpose');

    Route::post('purpose-save', [PurposeController::class, 'store'])
        ->name('purpose-save');

    Route::delete('purpose-delete', [PurposeController::class, 'delete'])
        ->name('purpose-delete');


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



    Route::get('service-type', [ServiceTypeController::class, 'create'])
        ->name('service-type');

    Route::post('service-type-save', [ServiceTypeController::class, 'store'])
        ->name('service-type-save');

    Route::delete('service-type-delete', [ServiceTypeController::class, 'delete'])
        ->name('service-type-delete');



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


    Route::get('invoice/{download}/{provider_id}/{event_id}', [ProviderController::class, 'invoicingPdf'])

        ->name('invoice');


    Route::post('invoice-email', [ProviderController::class, 'invoicingPdf'])
        ->name('invoice-email');


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

    Route::get('create-link/{download}/{provider_id}/{event_id}/{link}', [BudgetController::class, 'createLink'])
        ->name('create-link');

    Route::post('create-link-email', [BudgetController::class, 'createLink'])
        ->name('create-link-email');

    Route::get('proposal-hotel/{download}/{provider_id}/{event_id}', [ProviderController::class, 'proposalPdf'])
        ->name('proposal-hotel');


    Route::post('proposal-hotel-email', [ProviderController::class, 'proposalPdf'])
        ->name('proposal-hotel-email');
    //FIM HOTEL

    //dashboard
    Route::get('/dashboard-data', [HomeController::class, 'fetchDashboardData'])
        ->name('dashboard-data');

    Route::get('dash-pending-validate', [HomeController::class, 'pendingValidate'])
        ->name('dash-pending-validate');

    Route::get('dash-event-status', [HomeController::class, 'eventStatus'])
        ->name('dash-event-status');

    Route::get('dash-wait-approval', [HomeController::class, 'waitApproval'])
        ->name('dash-wait-approval');

    Route::get('dash-by-months', [HomeController::class, 'byMonths'])
        ->name('dash-by-months');

    Route::get('dash-links-approved', [HomeController::class, 'linksApproved'])
        ->name('dash-links-approved');

    Route::get('dash-users-groups', [HomeController::class, 'userGroups'])
        ->name('dash-users-groups');


    //FIM dashboard

    //status
    Route::get('status-history/{table}/{table_id}', [StatusHistoryController::class, 'listHistory'])
        ->name('status-history');


    Route::post('event-status-save', [StatusHistoryController::class, 'statusStore'])
        ->name('event-status-save');


    Route::post('send-mail', [StatusHistoryController::class, 'sendMail'])
        ->name('event-status-send-email');

    //FIM status
});
