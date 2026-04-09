<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Rutas de autenticación (sin registro público)
Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Catálogos
    Route::resource('catalogos/categorias', CategoriaController::class)
        ->names('catalogos.categorias');

    Route::resource('catalogos/marcas', MarcaController::class)
        ->names('catalogos.marcas');

});