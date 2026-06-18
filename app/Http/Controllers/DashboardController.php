<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\CajaResumen;
use Inertia\Inertia;

class DashboardController extends Controller
{
    // Serializa una venta a lo que necesita el dashboard
    private function ventaResumen(Venta $venta): array
    {
        return [
            'id' => $venta->id,
            'folio' => $venta->folio,
            'total' => (float) $venta->total,
            'metodo_pago' => $venta->metodo_pago,
            'hora' => $venta->created_at->isoFormat('HH:mm'),
            'cajero' => $venta->usuario->nombre ?? '—',
        ];
    }

    public function index()
    {
        $hoy = now()->toDateString();
        $userId = auth()->id();
        $esAdmin = auth()->user()->esAdmin();

        // Métricas globales: solo se calculan para admin (la vista
        // las oculta para cajeros, no tiene caso ejecutar las queries)
        $ventasHoy = 0;
        $totalHoy = 0;
        $productosActivos = 0;
        $stockBajo = 0;
        $ultimasVentas = collect();
        $ultimos7Dias = collect();
        $masVendidos = collect();

        if ($esAdmin) {
            $ventasHoy = Venta::whereDate('created_at', $hoy)->count();
            $totalHoy = Venta::whereDate('created_at', $hoy)->sum('total');
            $productosActivos = Producto::where('activo', true)->count();
            $stockBajo = Producto::where('activo', true)
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->count();
            $ultimasVentas = Venta::with('usuario')
                ->whereDate('created_at', $hoy)
                ->latest()
                ->limit(5)
                ->get();

            // Una sola query agrupada para los últimos 7 días
            $porDia = Venta::where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as fecha, COALESCE(SUM(total), 0) as total, COUNT(*) as ventas')
                ->groupBy('fecha')
                ->get()
                ->keyBy('fecha');

            $ultimos7Dias = collect(range(6, 0))->map(function ($dias) use ($porDia) {
                $fecha = now()->subDays($dias)->toDateString();
                $dia = $porDia->get($fecha);

                return [
                    'fecha' => now()->subDays($dias)->isoFormat('D MMM'),
                    'total' => $dia ? (float) $dia->total : 0,
                    'ventas' => $dia ? (int) $dia->ventas : 0,
                ];
            });

            $masVendidos = DetalleVenta::with('producto')
                ->selectRaw('producto_id, SUM(cantidad) as total_vendido, SUM(importe) as total_importe')
                ->groupBy('producto_id')
                ->orderByDesc('total_vendido')
                ->limit(5)
                ->get();
        }

        // Métricas propias del cajero autenticado (desde su último corte),
        // con el mismo cálculo que usan Caja y Cortes
        $resumen = CajaResumen::paraUsuario($userId);
        $ultimoCorte = $resumen['ultimoCorte'];

        $misVentasHoy = $resumen['numVentas'];
        $miEfectivo = $resumen['ventasEfectivo'];
        $miTarjeta = $resumen['tarjetaEsperada'];
        $miTotalHoy = $miEfectivo + $miTarjeta;

        $misUltimasVentas = Venta::where('created_at', $resumen['opInicio'], $resumen['fechaInicio'])
            ->where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        // Efectivo esperado en caja: ventas en efectivo + ingresos - retiros + fondo
        $miEfectivoModal = $resumen['efectivoDisponible'];

        return Inertia::render('Dashboard', [
            'ventasHoy' => $ventasHoy,
            'totalHoy' => (float) $totalHoy,
            'productosActivos' => $productosActivos,
            'stockBajo' => $stockBajo,
            'ultimasVentas' => $ultimasVentas->map(fn ($v) => $this->ventaResumen($v))->values(),
            'ultimos7Dias' => $ultimos7Dias->values(),
            'masVendidos' => $masVendidos->map(fn ($d) => [
                'nombre' => $d->producto->nombre ?? '—',
                'total_vendido' => (float) $d->total_vendido,
                'total_importe' => (float) $d->total_importe,
            ])->values(),
            'misVentasHoy' => $misVentasHoy,
            'miTotalHoy' => (float) $miTotalHoy,
            'miEfectivo' => (float) $miEfectivo,
            'miTarjeta' => (float) $miTarjeta,
            'misUltimasVentas' => $misUltimasVentas->map(fn ($v) => $this->ventaResumen($v))->values(),
            'ultimoCorte' => $ultimoCorte ? [
                'fecha' => $ultimoCorte->fecha_corte->isoFormat('D MMM HH:mm'),
                'dinero_en_caja' => (float) ($ultimoCorte->dinero_en_caja ?? 0),
            ] : null,
            'miEfectivoModal' => (float) $miEfectivoModal,
        ]);
    }
}
