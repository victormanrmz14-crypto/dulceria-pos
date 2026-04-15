<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CorteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cortes de caja (admin ve todos, cajero solo los suyos)
    Route::post('/cortes',          [CorteController::class, 'store'])->name('cortes.store');
    Route::get('/cortes',           [CorteController::class, 'index'])->name('cortes.index');
    Route::get('/cortes/{corte}',   [CorteController::class, 'show'])->name('cortes.show');

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

        Route::resource('catalogos/proveedores', ProveedorController::class)
            ->names('catalogos.proveedores')
            ->parameters(['proveedores' => 'proveedor']);

        Route::resource('productos', ProductoController::class);
        Route::post('productos/{producto}/notificar-proveedor', [ProductoController::class, 'notificarProveedor'])
            ->name('productos.notificar-proveedor');

        Route::resource('usuarios', UsuarioController::class)
            ->except(['show']);

        // Rutas de /profile deshabilitadas: usan vistas Breeze con Tailwind que no carga en este proyecto
        // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    });

});

require __DIR__.'/auth.php';