<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Venta;
use Illuminate\Http\Request;

class CorteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'efectivo_contado' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'dinero_en_caja'   => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'notas'            => ['nullable', 'string', 'max:500'],
        ], [
            'efectivo_contado.numeric' => 'El efectivo contado debe ser un número.',
            'efectivo_contado.min'     => 'El efectivo contado no puede ser negativo.',
            'dinero_en_caja.numeric'   => 'El dinero en caja debe ser un número.',
            'dinero_en_caja.min'       => 'El dinero en caja no puede ser negativo.',
            'notas.max'                => 'Las notas no pueden exceder 500 caracteres.',
        ]);

        $userId     = auth()->id();
        $fechaCorte = now();

        // Inicio del período: último corte del usuario o inicio del día actual
        $ultimoCorte = CorteCaja::where('user_id', $userId)
            ->latest('fecha_corte')
            ->first();

        $fechaInicio = $ultimoCorte
            ? $ultimoCorte->fecha_corte
            : now()->startOfDay();

        // El período es exclusivo en el inicio cuando viene de un corte previo,
        // para que una venta en el segundo exacto del corte no cuente dos veces
        $opInicio = $ultimoCorte ? '>' : '>=';

        // Ventas del usuario en ese período
        $ventas = Venta::where('user_id', $userId)
            ->where('created_at', $opInicio, $fechaInicio)
            ->where('created_at', '<=', $fechaCorte)
            ->get();

        $movimientos = MovimientoCaja::where('user_id', $userId)
            ->where('created_at', $opInicio, $fechaInicio)
            ->where('created_at', '<=', $fechaCorte)
            ->get();

        // Evitar cortes vacíos: sin ventas ni movimientos no hay nada que cortar
        if ($ventas->isEmpty() && $movimientos->isEmpty()) {
            return back()->with('error', 'No hay ventas ni movimientos desde el último corte.');
        }

        $ingresosCaja = $movimientos->where('tipo', 'ingreso')->sum('monto');
        $retirosCaja  = $movimientos->where('tipo', 'retiro')->sum('monto');

        $corte = CorteCaja::create([
            'user_id'           => $userId,
            'fecha_inicio'      => $fechaInicio,
            'fecha_corte'       => $fechaCorte,
            'num_transacciones' => $ventas->count(),
            'total_efectivo'    => $ventas->where('metodo_pago', 'efectivo')->sum('total') + $ingresosCaja - $retirosCaja,
            'total_tarjeta'     => $ventas->where('metodo_pago', 'tarjeta')->sum('total'),
            'total_general'     => $ventas->sum('total') + $ingresosCaja - $retirosCaja,
            'notas'             => $request->input('notas'),
            'efectivo_contado'  => $request->input('efectivo_contado'),
            'dinero_en_caja'    => $request->input('dinero_en_caja'),
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

        $cortes = $query->paginate(15)->through(fn ($c) => [
            'id'                => $c->id,
            'cajero'            => $c->usuario->nombre ?? '—',
            'fecha'             => $c->fecha_corte->isoFormat('DD/MM/YYYY HH:mm'),
            'num_transacciones' => $c->num_transacciones,
            'total_efectivo'    => (float) $c->total_efectivo,
            'total_tarjeta'     => (float) $c->total_tarjeta,
            'total_general'     => (float) $c->total_general,
        ]);

        return \Inertia\Inertia::render('Cortes/Index', ['cortes' => $cortes]);
    }

    public function show(CorteCaja $corte)
    {
        // Cajero solo puede ver sus propios cortes
        if (auth()->user()->rol !== 'admin' && $corte->user_id !== auth()->id()) {
            abort(403);
        }

        // Ventas incluidas en el período del corte.
        // Mismo criterio que al generar el corte: si hubo un corte previo el
        // inicio es exclusivo para no mostrar ventas que pertenecen al anterior.
        $huboCortePrevio = CorteCaja::where('user_id', $corte->user_id)
            ->where('fecha_corte', '<=', $corte->fecha_inicio)
            ->where('id', '!=', $corte->id)
            ->exists();

        $ventas = Venta::where('user_id', $corte->user_id)
            ->where('created_at', $huboCortePrevio ? '>' : '>=', $corte->fecha_inicio)
            ->where('created_at', '<=', $corte->fecha_corte)
            ->latest()
            ->get();

        $diferencia = $corte->efectivo_contado !== null
            ? round((float) $corte->efectivo_contado - (float) $corte->total_efectivo, 2)
            : null;

        return \Inertia\Inertia::render('Cortes/Show', [
            'corte' => [
                'id'                => $corte->id,
                'cajero'            => $corte->usuario->nombre ?? '—',
                'fecha_inicio'      => $corte->fecha_inicio->isoFormat('DD/MM/YYYY HH:mm'),
                'fecha_corte'       => $corte->fecha_corte->isoFormat('DD/MM/YYYY HH:mm'),
                'num_transacciones' => $corte->num_transacciones,
                'total_efectivo'    => (float) $corte->total_efectivo,
                'total_tarjeta'     => (float) $corte->total_tarjeta,
                'total_general'     => (float) $corte->total_general,
                'efectivo_contado'  => $corte->efectivo_contado !== null ? (float) $corte->efectivo_contado : null,
                'dinero_en_caja'    => $corte->dinero_en_caja !== null ? (float) $corte->dinero_en_caja : null,
                'diferencia'        => $diferencia,
                'notas'             => $corte->notas,
            ],
            'ventas' => $ventas->map(fn ($v) => [
                'id'          => $v->id,
                'folio'       => $v->folio,
                'total'       => (float) $v->total,
                'metodo_pago' => $v->metodo_pago,
                'hora'        => $v->created_at->isoFormat('DD/MM HH:mm'),
            ])->values(),
        ]);
    }
}
