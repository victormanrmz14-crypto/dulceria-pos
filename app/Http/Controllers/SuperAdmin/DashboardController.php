<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\SuperAdmin\Analytics;
use Illuminate\Database\QueryException;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Analytics $analytics)
    {
        try {
            $sales = $analytics->sales()->where('created_at', '>=', $analytics->start);
            $volume = (clone $sales)->selectRaw('COUNT(*) as ventas, COALESCE(SUM(total), 0) as importe')->first();

            return Inertia::render('SuperAdmin/Dashboard', [
                'periodo' => $analytics->period(),
                'kpis' => [
                    'registradas' => Tenant::count(),
                    'habilitadas' => Tenant::where('activo', true)->count(),
                    'con_ventas' => (clone $sales)->distinct()->count('tenant_id'),
                    'sin_primera_venta' => Tenant::whereNotIn('id', $analytics->sales()->select('tenant_id'))->count(),
                    'nuevas' => Tenant::whereBetween('created_at', [$analytics->start, $analytics->now])->count(),
                    'atencion' => $analytics->attention(Tenant::query())->count(),
                    'ventas' => (int) $volume->ventas, 'importe' => round((float) $volume->importe, 2),
                ],
                'grafica' => $analytics->series(),
                'alertas' => $analytics->attention($analytics->tenants())->latest()->limit(6)->get()->map(fn ($t) => $analytics->row($t)),
                'criterios' => $analytics->criteria(),
            ]);
        } catch (QueryException $exception) {
            report($exception);

            return Inertia::render('SuperAdmin/Dashboard', [
                'periodo' => $analytics->period(), 'kpis' => null, 'grafica' => null, 'alertas' => null, 'criterios' => $analytics->criteria(),
            ]);
        }
    }
}
