<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReporteController extends Controller
{
    // Máximo de días permitidos en un período personalizado
    private const MAX_DIAS_PERIODO = 366;

    public function index(Request $request)
    {
        $request->validate([
            'periodo' => 'nullable|string|in:hoy,ayer,7dias,30dias,personalizado',
            'desde'   => 'nullable|date',
            'hasta'   => 'nullable|date',
        ], [
            'desde.date' => 'La fecha inicial no es válida.',
            'hasta.date' => 'La fecha final no es válida.',
        ]);

        // Período seleccionado
        $periodo = $request->get('periodo', '7dias');

        [$fechaInicio, $fechaFin] = $this->obtenerFechas($periodo, $request);

        // Métricas principales en una sola query
        $metricas = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw("
                COUNT(*)                                                          as total_ventas,
                COALESCE(SUM(total), 0)                                           as total_ingresos,
                COALESCE(SUM(CASE WHEN metodo_pago = 'efectivo' THEN total END), 0) as total_efectivo,
                COALESCE(SUM(CASE WHEN metodo_pago = 'tarjeta'  THEN total END), 0) as total_tarjeta
            ")
            ->first();

        $totalVentas   = (int) $metricas->total_ventas;
        $totalIngresos = (float) $metricas->total_ingresos;
        $totalEfectivo = (float) $metricas->total_efectivo;
        $totalTarjeta  = (float) $metricas->total_tarjeta;

        // Gráfica por día: una sola query agrupada en vez de 2 por día
        $porDia = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('DATE(created_at) as fecha, COALESCE(SUM(total), 0) as total, COUNT(*) as ventas')
            ->groupBy('fecha')
            ->get()
            ->keyBy('fecha');

        $dias    = [];
        $current = $fechaInicio->copy()->startOfDay();
        $fin     = $fechaFin->copy()->startOfDay();

        while ($current->lte($fin)) {
            $dia    = $porDia->get($current->toDateString());
            $dias[] = [
                'fecha'  => $current->isoFormat('D MMM'),
                'total'  => $dia ? (float) $dia->total : 0,
                'ventas' => $dia ? (int) $dia->ventas : 0,
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
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($v) => [
                'id'          => $v->id,
                'folio'       => $v->folio,
                'total'       => (float) $v->total,
                'metodo_pago' => $v->metodo_pago,
                'fecha'       => $v->created_at->isoFormat('DD/MM/YYYY HH:mm'),
                'cajero'      => $v->usuario->nombre ?? '—',
            ]);

        return \Inertia\Inertia::render('Reportes/Index', [
            'periodo'       => $periodo,
            'desde'         => $request->get('desde', ''),
            'hasta'         => $request->get('hasta', ''),
            'rangoTexto'    => $fechaInicio->isoFormat('D [de] MMMM') . ' — ' . $fechaFin->isoFormat('D [de] MMMM [de] YYYY'),
            'totalVentas'   => $totalVentas,
            'totalIngresos' => $totalIngresos,
            'totalEfectivo' => $totalEfectivo,
            'totalTarjeta'  => $totalTarjeta,
            'graficaDias'   => $graficaDias->values(),
            'masVendidos'   => $masVendidos->map(fn ($d) => [
                'nombre'        => $d->producto->nombre ?? '—',
                'total_vendido' => (float) $d->total_vendido,
                'total_importe' => (float) $d->total_importe,
            ])->values(),
            'ventas'        => $ventas,
        ]);
    }

    private function obtenerFechas(string $periodo, Request $request): array
    {
        return match($periodo) {
            'hoy'      => [now()->startOfDay(),           now()->endOfDay()],
            'ayer'     => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            '7dias'    => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30dias'   => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'personalizado' => $this->rangoPersonalizado($request),
            default    => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
        };
    }

    private function rangoPersonalizado(Request $request): array
    {
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        if (empty($desde) || empty($hasta)) {
            return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
        }

        $inicio = Carbon::parse($desde)->startOfDay();
        $fin    = Carbon::parse($hasta)->endOfDay();

        // Si vienen invertidas, intercambiarlas
        if ($inicio->gt($fin)) {
            [$inicio, $fin] = [$fin->copy()->startOfDay(), $inicio->copy()->endOfDay()];
        }

        // Limitar el rango para no generar gráficas de miles de días
        if ($inicio->diffInDays($fin) > self::MAX_DIAS_PERIODO) {
            $inicio = $fin->copy()->subDays(self::MAX_DIAS_PERIODO)->startOfDay();
        }

        return [$inicio, $fin];
    }
}
