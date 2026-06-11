<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function index()
    {
        $userId  = auth()->id();
        $resumen = $this->resumenCaja($userId);

        $movimientos = MovimientoCaja::with('usuario')
            ->where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get();

        return \Inertia\Inertia::render('Caja/Index', [
            'efectivoEsperado' => $resumen['efectivoDisponible'],
            'tarjetaEsperada'  => $resumen['tarjetaEsperada'],
            'ventasEfectivo'   => $resumen['ventasEfectivo'],
            'fondoCaja'        => $resumen['fondoCaja'],
            'ingresos'         => $resumen['ingresos'],
            'retiros'          => $resumen['retiros'],
            'ultimoCorte'      => $resumen['ultimoCorte'] ? [
                'fecha' => $resumen['ultimoCorte']->fecha_corte->isoFormat('D MMM [a las] HH:mm'),
            ] : null,
            'movimientos'      => $movimientos->map(fn ($m) => [
                'id'     => $m->id,
                'tipo'   => $m->tipo,
                'monto'  => (float) $m->monto,
                'motivo' => $m->motivo,
                'fecha'  => $m->created_at->isoFormat('D MMM HH:mm'),
            ])->values(),
        ]);
    }

    public function ingreso(Request $request)
    {
        $request->validate([
            'monto'  => 'required|numeric|min:0.01|max:9999999.99',
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
            'monto'  => 'required|numeric|min:0.01|max:9999999.99',
            'motivo' => 'nullable|string|max:255',
        ]);

        $userId = auth()->id();

        try {
            DB::transaction(function () use ($request, $userId) {
                // Lock sobre el usuario: serializa retiros simultáneos del mismo
                // cajero para que dos retiros no pasen la validación a la vez
                User::lockForUpdate()->find($userId);

                $resumen = $this->resumenCaja($userId);

                if ((float) $request->monto > $resumen['efectivoDisponible']) {
                    throw new \RuntimeException(
                        'El retiro ($' . number_format((float) $request->monto, 2) . ') supera el efectivo disponible ($' . number_format($resumen['efectivoDisponible'], 2) . ').'
                    );
                }

                MovimientoCaja::create([
                    'user_id' => $userId,
                    'tipo'    => 'retiro',
                    'monto'   => $request->monto,
                    'motivo'  => $request->motivo,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('caja.index')
            ->with('success', 'Retiro registrado correctamente.');
    }

    /**
     * Resumen de caja del usuario desde su último corte (o inicio del día).
     * El inicio es exclusivo cuando viene de un corte previo, para no contar
     * dos veces una venta registrada en el segundo exacto del corte.
     */
    private function resumenCaja(int $userId): array
    {
        $ultimoCorte = CorteCaja::where('user_id', $userId)
            ->latest('fecha_corte')
            ->first();

        $fechaInicio = $ultimoCorte
            ? $ultimoCorte->fecha_corte
            : now()->startOfDay();

        $opInicio = $ultimoCorte ? '>' : '>=';

        $ventasEfectivo = Venta::where('user_id', $userId)
            ->where('created_at', $opInicio, $fechaInicio)
            ->where('metodo_pago', 'efectivo')
            ->sum('total');

        $tarjetaEsperada = Venta::where('user_id', $userId)
            ->where('created_at', $opInicio, $fechaInicio)
            ->where('metodo_pago', 'tarjeta')
            ->sum('total');

        $ingresos = MovimientoCaja::where('user_id', $userId)
            ->where('created_at', $opInicio, $fechaInicio)
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $retiros = MovimientoCaja::where('user_id', $userId)
            ->where('created_at', $opInicio, $fechaInicio)
            ->where('tipo', 'retiro')
            ->sum('monto');

        $fondoCaja = $ultimoCorte ? (float) ($ultimoCorte->dinero_en_caja ?? 0) : 0;

        return [
            'ultimoCorte'        => $ultimoCorte,
            'fechaInicio'        => $fechaInicio,
            'ventasEfectivo'     => (float) $ventasEfectivo,
            'tarjetaEsperada'    => (float) $tarjetaEsperada,
            'ingresos'           => (float) $ingresos,
            'retiros'            => (float) $retiros,
            'fondoCaja'          => $fondoCaja,
            'efectivoDisponible' => (float) $ventasEfectivo + (float) $ingresos - (float) $retiros + $fondoCaja,
        ];
    }
}
