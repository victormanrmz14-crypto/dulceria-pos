<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CajaController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $ultimoCorte = CorteCaja::where('user_id', $userId)
            ->latest('fecha_corte')
            ->first();

        $fechaInicio = $ultimoCorte
            ? Carbon::parse($ultimoCorte->fecha_corte)->setTimezone('America/Mexico_City')
            : now('America/Mexico_City')->startOfDay();

        $ventasEfectivo = Venta::where('user_id', $userId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('metodo_pago', 'efectivo')
            ->sum('total');

        $tarjetaEsperada = Venta::where('user_id', $userId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('metodo_pago', 'tarjeta')
            ->sum('total');

        $ingresos = MovimientoCaja::where('user_id', $userId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $retiros = MovimientoCaja::where('user_id', $userId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('tipo', 'retiro')
            ->sum('monto');

        $fondoCaja = $ultimoCorte ? (float) ($ultimoCorte->dinero_en_caja ?? 0) : 0;

        $efectivoEsperado = $ventasEfectivo + $ingresos - $retiros + $fondoCaja;

        $movimientos = MovimientoCaja::with('usuario')
            ->where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get();

        return view('caja.index', compact(
            'efectivoEsperado',
            'tarjetaEsperada',
            'movimientos',
            'ultimoCorte',
            'ingresos',
            'retiros',
        ));
    }

    public function ingreso(Request $request)
    {
        $request->validate([
            'monto'  => 'required|numeric|min:0.01',
            'motivo' => 'nullable|string|max:255',
        ]);

        MovimientoCaja::create([
            'user_id' => auth()->id(),
            'tipo'    => 'ingreso',
            'monto'   => $request->monto,
            'motivo'  => $request->motivo,
        ]);

        return redirect()->route('caja.index')
            ->with('success', 'Ingreso registrado correctamente.');
    }

    public function retiro(Request $request)
    {
        $request->validate([
            'monto'  => 'required|numeric|min:0.01',
            'motivo' => 'nullable|string|max:255',
        ]);

        // Calcular efectivo disponible actual
        $userId = auth()->id();

        $ultimoCorte = CorteCaja::where('user_id', $userId)
            ->latest('fecha_corte')
            ->first();

        $fechaInicio = $ultimoCorte
            ? Carbon::parse($ultimoCorte->fecha_corte)->setTimezone('America/Mexico_City')
            : now('America/Mexico_City')->startOfDay();

        $ventasEfectivo = Venta::where('user_id', $userId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('metodo_pago', 'efectivo')
            ->sum('total');

        $ingresos = MovimientoCaja::where('user_id', $userId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $retirosPrevios = MovimientoCaja::where('user_id', $userId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('tipo', 'retiro')
            ->sum('monto');

        $fondoCaja = $ultimoCorte ? (float) ($ultimoCorte->dinero_en_caja ?? 0) : 0;
        $efectivoDisponible = $ventasEfectivo + $ingresos - $retirosPrevios + $fondoCaja;

        if ((float) $request->monto > $efectivoDisponible) {
            return back()
                ->withInput()
                ->with('error', 'El retiro ($' . number_format($request->monto, 2) . ') supera el efectivo disponible ($' . number_format($efectivoDisponible, 2) . ').');
        }

        MovimientoCaja::create([
            'user_id' => auth()->id(),
            'tipo'    => 'retiro',
            'monto'   => $request->monto,
            'motivo'  => $request->motivo,
        ]);

        return redirect()->route('caja.index')
            ->with('success', 'Retiro registrado correctamente.');
    }
}
