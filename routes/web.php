<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Models\DetalleVenta;
use App\Http\Controllers\ReporteController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

Route::get('/dashboard', function () {
    $hoy = now()->toDateString();
    $userId = auth()->id();

    // Datos admin
    $ventasHoy        = \App\Models\Venta::whereDate('created_at', $hoy)->count();
    $totalHoy         = \App\Models\Venta::whereDate('created_at', $hoy)->sum('total');
    $productosActivos = \App\Models\Producto::where('activo', true)->count();
    $stockBajo        = \App\Models\Producto::where('activo', true)
                          ->whereColumn('stock', '<=', 'stock_minimo')->count();
    $ultimasVentas    = \App\Models\Venta::with('usuario')
                          ->whereDate('created_at', $hoy)
                          ->latest()->limit(5)->get();

    $ultimos7Dias = collect(range(6, 0))->map(function ($dias) {
        $fecha = now()->subDays($dias)->toDateString();
        return [
            'fecha'  => now()->subDays($dias)->isoFormat('D MMM'),
            'total'  => \App\Models\Venta::whereDate('created_at', $fecha)->sum('total'),
            'ventas' => \App\Models\Venta::whereDate('created_at', $fecha)->count(),
        ];
    });

    $masVendidos = \App\Models\DetalleVenta::with('producto')
        ->selectRaw('producto_id, SUM(cantidad) as total_vendido, SUM(importe) as total_importe')
        ->groupBy('producto_id')
        ->orderByDesc('total_vendido')
        ->limit(5)
        ->get();

    // Datos cajero
    $misVentasHoy     = \App\Models\Venta::whereDate('created_at', $hoy)
                          ->where('user_id', $userId)->count();
    $miTotalHoy       = \App\Models\Venta::whereDate('created_at', $hoy)
                          ->where('user_id', $userId)->sum('total');
    $miEfectivo       = \App\Models\Venta::whereDate('created_at', $hoy)
                          ->where('user_id', $userId)
                          ->where('metodo_pago', 'efectivo')->sum('total');
    $miTarjeta        = \App\Models\Venta::whereDate('created_at', $hoy)
                          ->where('user_id', $userId)
                          ->where('metodo_pago', 'tarjeta')->sum('total');
    $misUltimasVentas = \App\Models\Venta::whereDate('created_at', $hoy)
                          ->where('user_id', $userId)
                          ->latest()->limit(5)->get();

    return view('dashboard', compact(
        'ventasHoy', 'totalHoy', 'productosActivos', 'stockBajo',
        'ultimasVentas', 'ultimos7Dias', 'masVendidos',
        'misVentasHoy', 'miTotalHoy', 'miEfectivo',
        'miTarjeta', 'misUltimasVentas'
    ));
})->name('dashboard');

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