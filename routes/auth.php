<?php

use App\Http\Controllers\Auth\AptoController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CategoryController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\CustomerController;
use App\Http\Controllers\Auth\CrdController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\EventController;
use App\Http\Controllers\Auth\HotelController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\ProfileUserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\VerifyEmailController;
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


    //Eventos
    Route::get('event-list', [EventController::class, 'list'])
        ->name('event-list');

    Route::get('event', [EventController::class, 'create'])
        ->name('event-create');

    Route::get('event/{id}/{tab?}', [EventController::class, 'create'])
        ->name('event-edit');

    Route::post('event-save', [EventController::class, 'store'])
        ->name('event-save');


    Route::delete('event-delete', [EventController::class, 'delete'])
        ->name('event-delete');


    //HOTEL
    Route::get('hotel', [HotelController::class, 'create'])
        ->name('hotel');

    Route::post('hotel-save', [HotelController::class, 'store'])
        ->name('hotel-save');

    Route::delete('hotel-delete', [HotelController::class, 'delete'])
        ->name('hotel-delete');
});
