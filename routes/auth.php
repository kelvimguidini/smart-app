<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\HomeController;
use App\Http\Controllers\Auth\ProviderController;
use App\Http\Controllers\Auth\StatusHistoryController;
use App\Http\Controllers\Auth\ProposalHistoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


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

    Route::get('role', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('role');

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

    // USER routes migrated to api.php (Angular)
    Route::get('register', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('register');

    Route::get('profile', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('profile');

    // ROLE routes migrated to api.php (Angular)

    //TABELS AUXILIARES
    Route::get('customer', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('customer');

    Route::get('crd', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('crd');

    Route::get('city', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('city');



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

    Route::get('broker', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('broker');


    // Broker Trans routes migrated to api.php (Angular)











    // Transport Service routes migrated to api.php (Angular)



    // Local save and delete routes migrated to api.php (Angular)


    // Service save and delete routes migrated to api.php (Angular)



    // Service Hall save and delete routes migrated to api.php (Angular)

    // Purpose Hall save and delete routes migrated to api.php (Angular)



    // Service Type save and delete routes migrated to api.php (Angular)

    // SPA Angular Pages routes registration for Ziggy compatibility
    $angularRoutes = [
        'service',
        'service-type',
        'local',
        'service-hall',
        'purpose-hall',
        'measure',
        'frequency',
        'service-add',
        'provider-service',
        'brand',
        'car-model',
        'vehicle',
        'transport-service',
        'broker-trans',
        'provider-transport',
        'currency',
        'customer-requesters',
        'customer-sectors',
        'customer-cost-centers'
    ];

    foreach ($angularRoutes as $route) {
        Route::get($route, function (Request $request) {
            $path = base_path('public/angular.html');
            if (file_exists($path)) {
                if ($request->header('X-Inertia')) {
                    return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
                }
                return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
            }
            abort(404);
        })->name($route);
    }

    //Eventos (SPA Angular)
    Route::get('event-list/{page?}', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('event-list');

    Route::post('event-list/{page?}', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('event-list.filter');

    Route::get('event', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('event-create');

    Route::get('event/{id}/{tab?}/{ehotel?}', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('event-edit');

    Route::get('invoice/{download}/{provider_id}/{event_id}/{type}', [ProviderController::class, 'invoicingPdf'])
        ->name('invoice');


    Route::post('invoice-email', [ProviderController::class, 'invoicingPdf'])
        ->name('invoice-email');


    //HOTEL
    Route::get('hotel', function (Request $request) {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        abort(404);
    })->name('hotel');

    Route::post('hotel-event-save', [ProviderController::class, 'storeEventProvider'])
        ->name('hotel-event-save');

    //ORÇAMENTO

    Route::get('create-link/{download}/{provider_id}/{event_id}/{link}/{type}', [ProviderController::class, 'createLink'])
        ->name('create-link');

    Route::post('create-link-email', [ProviderController::class, 'createLink'])
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


    Route::post('/update-provider-order', [ProviderController::class, 'updateOrder'])->name('update-provider-order');

    // routes/web.php
    Route::get('history/{event_id}', [ProposalHistoryController::class, 'getHistory'])
        ->name('history-get');


    Route::post('history/restore', [ProposalHistoryController::class, 'restore'])
        ->name('history-restore');
});
