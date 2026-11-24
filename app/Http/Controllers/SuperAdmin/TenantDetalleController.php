<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SuperAdmin\Analytics;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TenantDetalleController extends Controller
{
    public function show(Tenant $tenant, Analytics $analytics): Response
    {
        try {
            return $this->detail($tenant, $analytics);
        } catch (QueryException $exception) {
            report($exception);

            return Inertia::render('SuperAdmin/TenantDetalle', [
                'tenant' => $this->identity($tenant), 'stats' => null, 'admin' => null,
                'ultimasVentas' => null, 'grafica' => null, 'periodo' => $analytics->period(), 'operacion' => null,
            ]);
        }
    }

    private function identity(Tenant $tenant): array
    {
        return [
            'id' => $tenant->id, 'nombre' => $tenant->nombre, 'activo' => $tenant->activo,
            'plan' => $tenant->plan, 'plan_label' => $tenant->planLabel(),
            'plan_expira_en' => $tenant->plan_expira_en?->isoFormat('D MMM YYYY'),
            'notas' => $tenant->notas, 'creado_en' => $tenant->created_at?->isoFormat('D MMM YYYY'),
            'dias_trial' => $tenant->diasRestantesTrial(),
        ];
    }

    private function detail(Tenant $tenant, Analytics $analytics): Response
    {
        $operationalTenant = $analytics->tenants()->findOrFail($tenant->id);
        $admin = $operationalTenant->users->first();
        $sales = $analytics->sales()->where('tenant_id', $tenant->id);
        $summary = (clone $sales)->selectRaw(
            'COUNT(*) as historicas, COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) as ventas_7, COALESCE(SUM(CASE WHEN created_at >= ? THEN total ELSE 0 END), 0) as importe_30',
            [$analytics->now->subDays(config('superadmin.operations.short_activity_days') - 1)->startOfDay(), $analytics->start]
        )->first();
        $ultimasVentas = (clone $sales)->with('usuario')->latest()->orderByDesc('id')->limit(8)->get()->map(fn ($v) => [
            'id' => $v->id, 'folio' => $v->folio, 'total' => (float) $v->total,
            'metodo_pago' => $v->metodo_pago, 'cajero' => $v->usuario?->nombre ?? '—',
            'fecha' => $v->created_at->isoFormat('D MMM YYYY, HH:mm'),
        ]);
        $grafica = $analytics->series($tenant->id);
        $activation = $analytics->activation($operationalTenant);

        return Inertia::render('SuperAdmin/TenantDetalle', [
            'tenant' => $this->identity($tenant),
            'operacion' => [
                'activacion' => $activation,
                'senales' => $analytics->signals($operationalTenant),
                'actividad' => $analytics->row($operationalTenant)['actividad'],
            ],
            'admin' => $admin ? [
                'id' => $admin->id,
                'nombre' => trim("{$admin->nombre} {$admin->apellido}"),
                'email' => $admin->email,
                'activo' => $admin->activo,
            ] : null,
            'stats' => [
                'usuarios' => (int) $operationalTenant->users_count,
                'productos' => (int) $operationalTenant->productos_count,
                'ventas_30' => (int) $operationalTenant->ventas_30,
                'ventas_7' => (int) $summary->ventas_7,
                'primera_venta' => $analytics->date($operationalTenant->primera_venta),
                'primer_corte' => $activation[3]['evidencia'],
                'ventas_total' => (int) $summary->historicas,
                'importe_30' => round((float) $summary->importe_30, 2),
                'ultima_venta' => $analytics->date($operationalTenant->ultima_venta),
            ],
            'ultimasVentas' => $ultimasVentas->values(),
            'grafica' => $grafica,
            'periodo' => $analytics->period(),
        ]);
    }

    public function toggle(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['activo' => ! $tenant->activo]);
        $accion = $tenant->activo ? 'activado' : 'desactivado';
        AuditLogger::log('tenant_toggle', "Tenant '{$tenant->nombre}' {$accion}", $tenant->id, ['activo' => $tenant->activo]);

        return back()->with('success', "Dulcería {$accion} correctamente.");
    }

    public function guardarNotas(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate(['notas' => 'nullable|string|max:5000'], [
            'notas.max' => 'Las notas admiten hasta :max caracteres.',
        ]);
        $tenant->update(['notas' => $request->notas]);
        AuditLogger::log('notas_tenant', "Notas actualizadas para '{$tenant->nombre}'", $tenant->id);

        return back()->with('success', 'Notas guardadas.');
    }

    public function impersonar(Tenant $tenant): RedirectResponse
    {
        $admin = User::where('tenant_id', $tenant->id)->where('rol', 'admin')->first();
        if (! $admin) {
            return back()->with('error', 'Esta dulcería no tiene administrador asignado.');
        }

        AuditLogger::log('impersonacion_inicio', "Impersonando a '{$admin->email}' de '{$tenant->nombre}'", $tenant->id, ['target_user_id' => $admin->id]);

        session(['superadmin_original_id' => auth()->id()]);
        Auth::loginUsingId($admin->id);

        return redirect()->route('dashboard');
    }

    public function salirImpersonacion(): RedirectResponse
    {
        $originalId = session()->pull('superadmin_original_id');
        if (! $originalId) {
            return redirect()->route('superadmin.dashboard');
        }

        $original = User::find($originalId);
        if (! $original) {
            return redirect()->route('login');
        }

        AuditLogger::log('impersonacion_fin', "Fin de impersonación, volviendo a '{$original->email}'");
        Auth::loginUsingId($originalId);

        return redirect()->route('superadmin.dashboard');
    }
}
