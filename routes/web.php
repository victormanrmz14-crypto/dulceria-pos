<?php

use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenants;
use App\Http\Controllers\SuperAdmin\TenantDetalleController;
use App\Http\Controllers\SuperAdmin\UsuarioController as SuperAdminUsuarios;
use App\Http\Controllers\SuperAdmin\MonitoreoController;
use App\Http\Controllers\SuperAdmin\SoporteController;
use App\Http\Controllers\SuperAdmin\ComunicacionController;
use App\Http\Controllers\SuperAdmin\SaasController;
use App\Http\Controllers\SuperAdmin\AuditoriaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CajaController;
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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('landing');
})->name('landing');

Route::middleware(['auth', 'usuario.activo'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::post('/caja/ingreso', [CajaController::class, 'ingreso'])->name('caja.ingreso');
    Route::post('/caja/retiro', [CajaController::class, 'retiro'])->name('caja.retiro');

    Route::post('/cortes', [CorteController::class, 'store'])->name('cortes.store');
    Route::get('/cortes', [CorteController::class, 'index'])->name('cortes.index');
    Route::get('/cortes/{corte}', [CorteController::class, 'show'])->name('cortes.show');

    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/buscar-productos', [VentaController::class, 'buscarProductos'])->name('ventas.buscar');
    Route::get('/ventas/historial', [VentaController::class, 'historial'])->name('ventas.historial');
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');

    Route::middleware(['solo.admin'])->group(function () {
        Route::resource('catalogos/categorias', CategoriaController::class)->except(['show'])->names('catalogos.categorias');
        Route::resource('catalogos/marcas', MarcaController::class)->except(['show'])->names('catalogos.marcas');
        Route::resource('catalogos/proveedores', ProveedorController::class)->except(['show'])->names('catalogos.proveedores')->parameters(['proveedores' => 'proveedor']);
        Route::resource('productos', ProductoController::class)->except(['show']);
        Route::post('productos/{producto}/notificar-proveedor', [ProductoController::class, 'notificarProveedor'])->name('productos.notificar-proveedor');
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

        // Configuración de la dulcería
        Route::prefix('configuracion')->name('configuracion.')->group(function () {
            Route::get('/',             [ConfiguracionController::class, 'index'])->name('index');
            Route::post('/apariencia',  [ConfiguracionController::class, 'guardarApariencia'])->name('apariencia');
            Route::post('/negocio',     [ConfiguracionController::class, 'guardarNegocio'])->name('negocio');
            Route::post('/tickets',     [ConfiguracionController::class, 'guardarTickets'])->name('tickets');
            Route::post('/password',    [ConfiguracionController::class, 'cambiarPassword'])->name('password');
        });
    });
});

// ── Salir de impersonación (solo requiere auth, no solo.superadmin) ──────────
Route::middleware('auth')->post('/superadmin/salir-impersonacion', [TenantDetalleController::class, 'salirImpersonacion'])->name('superadmin.salir-impersonacion');

// ── Panel de Super Admin ──────────────────────────────────────────────────────
Route::middleware(['auth', 'solo.superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    // Panel general
    Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');

    // Monitoreo
    Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo');

    // Gestión de dulcerías
    Route::get('/dulcerias',                            [SuperAdminTenants::class,     'index'])->name('dulcerias');
    Route::get('/dulcerias/{tenant}',                   [TenantDetalleController::class,'show'])->name('dulcerias.show');
    Route::post('/dulcerias/{tenant}/toggle',           [TenantDetalleController::class,'toggle'])->name('dulcerias.toggle');
    Route::post('/dulcerias/{tenant}/notas',            [TenantDetalleController::class,'guardarNotas'])->name('dulcerias.notas');
    Route::post('/dulcerias/{tenant}/impersonar',       [TenantDetalleController::class,'impersonar'])->name('dulcerias.impersonar');

    // Usuarios
    Route::get('/usuarios', [SuperAdminUsuarios::class, 'index'])->name('usuarios');
    Route::post('/usuarios/{usuario}/reset-password',   [SoporteController::class,    'resetPassword'])->name('usuarios.reset-password');
    Route::post('/usuarios/{usuario}/reenviar-bienvenida', [SoporteController::class, 'reenviarBienvenida'])->name('usuarios.reenviar-bienvenida');

    // Soporte
    Route::get('/soporte', [SoporteController::class, 'index'])->name('soporte');

    // Comunicación
    Route::get('/comunicacion',                     [ComunicacionController::class, 'index'])->name('comunicacion');
    Route::post('/comunicacion',                    [ComunicacionController::class, 'store'])->name('comunicacion.store');
    Route::post('/comunicacion/{anuncio}/toggle',   [ComunicacionController::class, 'toggle'])->name('comunicacion.toggle');
    Route::delete('/comunicacion/{anuncio}',        [ComunicacionController::class, 'destroy'])->name('comunicacion.destroy');
    Route::post('/comunicacion/email-masivo',       [ComunicacionController::class, 'emailMasivo'])->name('comunicacion.masivo');

    // SaaS / Planes
    Route::get('/saas',                            [SaasController::class, 'index'])->name('saas');
    Route::post('/saas/{tenant}/plan',             [SaasController::class, 'actualizarPlan'])->name('saas.plan');

    // Auditoría
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria');
});

require __DIR__.'/auth.php';
