<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Ventas (accesible para admin y cajero)
    Route::get('/ventas',           [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/historial', [VentaController::class, 'historial'])->name('ventas.historial');
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');

    // Solo admin
    Route::middleware(['solo.admin'])->group(function () {
        Route::resource('catalogos/categorias', CategoriaController::class)
            ->names('catalogos.categorias');

        Route::resource('catalogos/marcas', MarcaController::class)
            ->names('catalogos.marcas');

        Route::resource('productos', ProductoController::class);

        Route::resource('usuarios', UsuarioController::class)
            ->except(['show']);

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    });

});

require __DIR__.'/auth.php';