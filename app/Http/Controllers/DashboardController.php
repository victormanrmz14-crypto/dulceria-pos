<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy    = now()->toDateString();
        $userId = auth()->id();

        // Métricas globales (visibles para admin)
        $ventasHoy        = Venta::whereDate('created_at', $hoy)->count();
        $totalHoy         = Venta::whereDate('created_at', $hoy)->sum('total');
        $productosActivos = Producto::where('activo', true)->count();
        $stockBajo        = Producto::where('activo', true)
                                ->whereColumn('stock', '<=', 'stock_minimo')
                                ->count();
        $ultimasVentas    = Venta::with('usuario')
                                ->whereDate('created_at', $hoy)
                                ->latest()
                                ->limit(5)
                                ->get();

        $ultimos7Dias = collect(range(6, 0))->map(function ($dias) {
            $fecha = now()->subDays($dias)->toDateString();
            return [
                'fecha'  => now()->subDays($dias)->isoFormat('D MMM'),
                'total'  => Venta::whereDate('created_at', $fecha)->sum('total'),
                'ventas' => Venta::whereDate('created_at', $fecha)->count(),
            ];
        });

        $masVendidos = DetalleVenta::with('producto')
            ->selectRaw('producto_id, SUM(cantidad) as total_vendido, SUM(importe) as total_importe')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        // Métricas propias del cajero autenticado
        $misVentasHoy     = Venta::whereDate('created_at', $hoy)->where('user_id', $userId)->count();
        $miTotalHoy       = Venta::whereDate('created_at', $hoy)->where('user_id', $userId)->sum('total');
        $miEfectivo       = Venta::whereDate('created_at', $hoy)
                                ->where('user_id', $userId)
                                ->where('metodo_pago', 'efectivo')
                                ->sum('total');
        $miTarjeta        = Venta::whereDate('created_at', $hoy)
                                ->where('user_id', $userId)
                                ->where('metodo_pago', 'tarjeta')
                                ->sum('total');
        $misUltimasVentas = Venta::whereDate('created_at', $hoy)
                                ->where('user_id', $userId)
                                ->latest()
                                ->limit(5)
                                ->get();

        return view('dashboard', compact(
            'ventasHoy', 'totalHoy', 'productosActivos', 'stockBajo',
            'ultimasVentas', 'ultimos7Dias', 'masVendidos',
            'misVentasHoy', 'miTotalHoy', 'miEfectivo',
            'miTarjeta', 'misUltimasVentas'
        ));
    }
}
