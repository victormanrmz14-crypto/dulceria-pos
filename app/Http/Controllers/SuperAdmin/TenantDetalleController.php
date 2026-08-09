<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Venta;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TenantDetalleController extends Controller
{
    public function show(Tenant $tenant): Response
    {
        $usuarios = User::where('tenant_id', $tenant->id)->get();
        $admin    = $usuarios->where('rol', 'admin')->sortBy('created_at')->first();

        $ventasMes    = Venta::where('tenant_id', $tenant->id)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $ventasTotal  = Venta::where('tenant_id', $tenant->id)->count();
        $ingresosMes  = Venta::where('tenant_id', $tenant->id)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total');
        $productos    = Producto::where('tenant_id', $tenant->id)->count();
        $ultimasVentas = Venta::with('usuario')->where('tenant_id', $tenant->id)->latest()->limit(8)->get()->map(fn ($v) => [
            'id'          => $v->id,
            'folio'       => $v->folio,
            'total'       => (float) $v->total,
            'metodo_pago' => $v->metodo_pago,
            'cajero'      => $v->usuario?->nombre ?? '—',
            'fecha'       => $v->created_at->isoFormat('D MMM HH:mm'),
        ]);

        // Gráfica: ventas últimos 14 días
        $ventasPorDia = Venta::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as ventas, SUM(total) as total')
            ->groupBy('fecha')->get()->keyBy('fecha');

        $grafica = collect(range(13, 0))->map(function ($d) use ($ventasPorDia) {
            $fecha = now()->subDays($d)->toDateString();
            $dia   = $ventasPorDia->get($fecha);
            return ['fecha' => now()->subDays($d)->isoFormat('D MMM'), 'ventas' => (int) ($dia?->ventas ?? 0), 'total' => (float) ($dia?->total ?? 0)];
        })->values();

        return Inertia::render('SuperAdmin/TenantDetalle', [
            'tenant' => [
                'id'             => $tenant->id,
                'nombre'         => $tenant->nombre,
                'activo'         => $tenant->activo,
                'plan'           => $tenant->plan,
                'plan_label'     => $tenant->planLabel(),
                'plan_expira_en' => $tenant->plan_expira_en?->isoFormat('D MMM YYYY'),
                'notas'          => $tenant->notas,
                'creado_en'      => $tenant->created_at->isoFormat('D MMM YYYY'),
                'dias_trial'     => $tenant->diasRestantesTrial(),
            ],
            'admin' => $admin ? [
                'id'     => $admin->id,
                'nombre' => trim("{$admin->nombre} {$admin->apellido}"),
                'email'  => $admin->email,
                'activo' => $admin->activo,
            ] : null,
            'stats' => [
                'usuarios'     => $usuarios->count(),
                'productos'    => $productos,
                'ventas_mes'   => $ventasMes,
                'ventas_total' => $ventasTotal,
                'ingresos_mes' => (float) $ingresosMes,
            ],
            'ultimasVentas' => $ultimasVentas->values(),
            'grafica'       => $grafica,
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
        $request->validate(['notas' => 'nullable|string|max:5000']);
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
