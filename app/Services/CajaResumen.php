<?php

namespace App\Services;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Venta;
use Carbon\CarbonInterface;

/**
 * Cálculo único del estado de caja de un usuario para su período actual
 * (desde su último corte, o desde el inicio del día si no tiene cortes).
 *
 * Lo usan CajaController, CorteController y DashboardController para que
 * "efectivo esperado" signifique lo mismo en todas las pantallas:
 * ventas en efectivo + ingresos manuales - retiros + fondo del corte anterior.
 */
class CajaResumen
{
    /**
     * El inicio del período es exclusivo cuando viene de un corte previo,
     * para no contar dos veces una venta registrada en el segundo exacto
     * del corte. $hasta acota el final (p. ej. la fecha de un corte en
     * proceso) para que todas las queries vean el mismo instante.
     */
    public static function paraUsuario(int $userId, ?CarbonInterface $hasta = null): array
    {
        $ultimoCorte = CorteCaja::where('user_id', $userId)
            ->latest('fecha_corte')
            ->first();

        $fechaInicio = $ultimoCorte
            ? $ultimoCorte->fecha_corte
            : now()->startOfDay();

        $opInicio = $ultimoCorte ? '>' : '>=';

        $ventas = Venta::where('user_id', $userId)
            ->where('created_at', $opInicio, $fechaInicio)
            ->when($hasta, fn ($q) => $q->where('created_at', '<=', $hasta))
            ->selectRaw("
                COUNT(*) as num,
                COALESCE(SUM(CASE WHEN metodo_pago = 'efectivo' THEN total END), 0) as efectivo,
                COALESCE(SUM(CASE WHEN metodo_pago = 'tarjeta'  THEN total END), 0) as tarjeta
            ")
            ->first();

        $movimientos = MovimientoCaja::where('user_id', $userId)
            ->where('created_at', $opInicio, $fechaInicio)
            ->when($hasta, fn ($q) => $q->where('created_at', '<=', $hasta))
            ->selectRaw("
                COUNT(*) as num,
                COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto END), 0) as ingresos,
                COALESCE(SUM(CASE WHEN tipo = 'retiro'  THEN monto END), 0) as retiros
            ")
            ->first();

        // Fondo que dejó el corte anterior: sigue dentro del cajón, por eso
        // forma parte del efectivo esperado
        $fondoCaja = $ultimoCorte ? (float) ($ultimoCorte->dinero_en_caja ?? 0) : 0.0;

        return [
            'ultimoCorte'        => $ultimoCorte,
            'fechaInicio'        => $fechaInicio,
            'opInicio'           => $opInicio,
            'numVentas'          => (int) $ventas->num,
            'numMovimientos'     => (int) $movimientos->num,
            'ventasEfectivo'     => (float) $ventas->efectivo,
            'tarjetaEsperada'    => (float) $ventas->tarjeta,
            'ingresos'           => (float) $movimientos->ingresos,
            'retiros'            => (float) $movimientos->retiros,
            'fondoCaja'          => $fondoCaja,
            'efectivoDisponible' => (float) $ventas->efectivo
                + (float) $movimientos->ingresos
                - (float) $movimientos->retiros
                + $fondoCaja,
        ];
    }
}
