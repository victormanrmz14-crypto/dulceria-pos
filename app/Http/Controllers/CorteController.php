<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\Venta;
use Illuminate\Http\Request;

class CorteController extends Controller
{
    public function store(Request $request)
    {
        $userId     = auth()->id();
        $fechaCorte = now();

        // Inicio del período: último corte del usuario o inicio del día actual
        $ultimoCorte = CorteCaja::where('user_id', $userId)
            ->latest('fecha_corte')
            ->first();

        $fechaInicio = $ultimoCorte
            ? $ultimoCorte->fecha_corte
            : now()->startOfDay();

        // Ventas del usuario en ese período
        $ventas = Venta::where('user_id', $userId)
            ->whereBetween('created_at', [$fechaInicio, $fechaCorte])
            ->get();

        $corte = CorteCaja::create([
            'user_id'           => $userId,
            'fecha_inicio'      => $fechaInicio,
            'fecha_corte'       => $fechaCorte,
            'num_transacciones' => $ventas->count(),
            'total_efectivo'    => $ventas->where('metodo_pago', 'efectivo')->sum('total'),
            'total_tarjeta'     => $ventas->where('metodo_pago', 'tarjeta')->sum('total'),
            'total_general'     => $ventas->sum('total'),
            'notas'             => $request->input('notas'),
        ]);

        return redirect()->route('cortes.show', $corte)
            ->with('success', 'Corte de caja generado correctamente.');
    }

    public function index()
    {
        $query = CorteCaja::with('usuario')->latest('fecha_corte');

        if (auth()->user()->rol !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        $cortes = $query->paginate(15);

        return view('cortes.index', compact('cortes'));
    }

    public function show(CorteCaja $corte)
    {
        // Cajero solo puede ver sus propios cortes
        if (auth()->user()->rol !== 'admin' && $corte->user_id !== auth()->id()) {
            abort(403);
        }

        // Ventas incluidas en el período del corte
        $ventas = Venta::where('user_id', $corte->user_id)
            ->whereBetween('created_at', [$corte->fecha_inicio, $corte->fecha_corte])
            ->latest()
            ->get();

        return view('cortes.show', compact('corte', 'ventas'));
    }
}
