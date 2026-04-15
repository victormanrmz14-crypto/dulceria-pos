<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        // Período seleccionado
        $periodo = $request->get('periodo', '7dias');

        [$fechaInicio, $fechaFin] = $this->obtenerFechas($periodo, $request);

        // Métricas principales
        $totalVentas    = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])->count();
        $totalIngresos  = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('total');
        $totalEfectivo  = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])
                            ->where('metodo_pago', 'efectivo')->sum('total');
        $totalTarjeta   = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])
                            ->where('metodo_pago', 'tarjeta')->sum('total');
        // Gráfica por día
        $inicio = $fechaInicio->copy()->startOfDay();
        $fin    = $fechaFin->copy()->startOfDay();
        $dias   = [];
        $current = $inicio->copy();

        while ($current->lte($fin)) {
            $dias[] = [
            'fecha'  => $current->isoFormat('D MMM'),
            'total'  => Venta::whereDate('created_at', $current->toDateString())->sum('total'),
            'ventas' => Venta::whereDate('created_at', $current->toDateString())->count(),
            ];
            $current->addDay();
        }

$graficaDias = collect($dias);
        // Productos más vendidos
        $masVendidos = DetalleVenta::with('producto')
            ->whereHas('venta', fn($q) => $q->whereBetween('created_at', [$fechaInicio, $fechaFin]))
            ->selectRaw('producto_id, SUM(cantidad) as total_vendido, SUM(importe) as total_importe')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        // Listado de ventas
        $ventas = Venta::with('usuario')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->latest()
            ->paginate(10);

        return view('reportes.index', compact(
            'periodo', 'fechaInicio', 'fechaFin',
            'totalVentas', 'totalIngresos', 'totalEfectivo', 'totalTarjeta',
            'graficaDias', 'masVendidos', 'ventas'
        ));
    }

    private function obtenerFechas(string $periodo, Request $request): array
    {
        return match($periodo) {
            'hoy'      => [now()->startOfDay(),           now()->endOfDay()],
            'ayer'     => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            '7dias'    => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30dias'   => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'personalizado' => (function () use ($request) {
                $desde = $request->get('desde');
                $hasta = $request->get('hasta');
                if (empty($desde) || empty($hasta)) {
                    return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
                }
                return [
                    Carbon::parse($desde)->startOfDay(),
                    Carbon::parse($hasta)->endOfDay(),
                ];
            })(),
            default    => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
        };
    }
}