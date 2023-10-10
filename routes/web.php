<?php

use App\Http\Controllers\Auth\ChaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'cors'])->name('dashboard');


Route::get('cha-revelacao', [ChaController::class, 'jogo'])
    ->name('cha-revelacao');


Route::get('salvar-menino-menina', [ChaController::class, 'create'])
    ->name('salvar-menino-menina');

Route::post('salvar-menino-menina', [ChaController::class, 'store'])
    ->name('salvar-menino-menina-save');


require __DIR__ . '/auth.php';
