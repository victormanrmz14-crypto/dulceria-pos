<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // KPIs
        $totalTenants   = Tenant::count();
        $tenantsMes     = Tenant::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count();
        $totalUsuarios  = User::whereNotNull('tenant_id')->count();
        $tenantsActivos = Tenant::whereHas('users', fn ($q) => $q->where('rol', 'admin')->where('activo', true))->count();
        $tenantsConProblemas = $totalTenants - $tenantsActivos;

        // Gráfica de registros — últimos 30 días
        $registrosPorDia = Tenant::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->get()
            ->keyBy('fecha');

        $grafica = collect(range(29, 0))->map(function ($dias) use ($registrosPorDia) {
            $fecha = now()->subDays($dias)->toDateString();
            return [
                'fecha' => now()->subDays($dias)->isoFormat('D MMM'),
                'total' => (int) ($registrosPorDia->get($fecha)?->total ?? 0),
            ];
        })->values();

        // Tabla diagnóstico completa
        $tenants = Tenant::withCount('users')
            ->with([
                'users' => fn ($q) => $q->where('rol', 'admin')->orderBy('created_at')->limit(1),
            ])
            ->latest()
            ->get()
            ->map(function (Tenant $t) {
                $admin = $t->users->first();

                $problemas = [];
                if (! $admin)               $problemas[] = 'Sin administrador asignado';
                if ($admin && ! $admin->activo) $problemas[] = 'Administrador desactivado';

                return [
                    'id'             => $t->id,
                    'nombre'         => $t->nombre,
                    'admin_nombre'   => $admin ? trim("{$admin->nombre} {$admin->apellido}") : null,
                    'admin_email'    => $admin?->email,
                    'admin_activo'   => $admin?->activo,
                    'usuarios'       => $t->users_count,
                    'problemas'      => $problemas,
                    'estado'         => count($problemas) > 0 ? 'problema' : 'ok',
                    'creado_en'      => $t->created_at->isoFormat('D MMM YYYY, HH:mm'),
                    'creado_diff'    => $t->created_at->diffForHumans(),
                ];
            });

        // Alertas: solo las que tienen problemas
        $alertas = $tenants->filter(fn ($t) => $t['estado'] === 'problema')->values();

        return Inertia::render('SuperAdmin/Dashboard', [
            'totalTenants'        => $totalTenants,
            'tenantsMes'          => $tenantsMes,
            'totalUsuarios'       => $totalUsuarios,
            'tenantsActivos'      => $tenantsActivos,
            'tenantsConProblemas' => $tenantsConProblemas,
            'grafica'             => $grafica,
            'tenants'             => $tenants->values(),
            'alertas'             => $alertas,
        ]);
    }
}
