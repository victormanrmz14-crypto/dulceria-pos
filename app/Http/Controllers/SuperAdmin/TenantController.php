<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount('users')
            ->with([
                'users' => fn ($q) => $q->where('rol', 'admin')->orderBy('created_at')->limit(1),
            ])
            ->latest()
            ->get()
            ->map(function (Tenant $t) {
                $admin = $t->users->first();

                $problemas = [];
                if (! $admin)                   $problemas[] = 'Sin administrador asignado';
                if ($admin && ! $admin->activo)  $problemas[] = 'Administrador desactivado';

                return [
                    'id'           => $t->id,
                    'nombre'       => $t->nombre,
                    'admin_nombre' => $admin ? trim("{$admin->nombre} {$admin->apellido}") : null,
                    'admin_email'  => $admin?->email,
                    'admin_activo' => $admin?->activo,
                    'usuarios'     => $t->users_count,
                    'problemas'    => $problemas,
                    'estado'       => count($problemas) > 0 ? 'problema' : 'ok',
                    'creado_en'    => $t->created_at->isoFormat('D MMM YYYY, HH:mm'),
                    'creado_diff'  => $t->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('SuperAdmin/Dulcerias', [
            'tenants' => $tenants->values(),
            'total'   => $tenants->count(),
            'conProblemas' => $tenants->where('estado', 'problema')->count(),
        ]);
    }
}
