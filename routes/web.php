<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

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

    Route::resource('productos', ProductoController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::resource('usuarios', UsuarioController::class)
        ->except(['show']);

});

require __DIR__.'/auth.php';