<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SaasController extends Controller
{
    public const PLANES = [
        'trial'  => ['label' => 'Trial',   'precio' => 0,   'max_usuarios' => 2,  'max_productos' => 50,  'ventas' => '100/mes'],
        'basico' => ['label' => 'Básico',  'precio' => 299, 'max_usuarios' => 5,  'max_productos' => 200, 'ventas' => 'Ilimitadas'],
        'pro'    => ['label' => 'Pro',     'precio' => 599, 'max_usuarios' => 999,'max_productos' => 999, 'ventas' => 'Ilimitadas'],
    ];

    public function index(): Response
    {
        $tenants = Tenant::withCount('users')->latest()->get()->map(fn (Tenant $t) => [
            'id'             => $t->id,
            'nombre'         => $t->nombre,
            'activo'         => $t->activo,
            'plan'           => $t->plan,
            'plan_label'     => $t->planLabel(),
            'plan_expira_en' => $t->plan_expira_en?->isoFormat('D MMM YYYY'),
            'plan_vencido'   => $t->plan_expira_en && $t->plan_expira_en->lt(now()),
            'dias_restantes' => $t->diasRestantesTrial(),
            'usuarios'       => $t->users_count,
            'creado_en'      => $t->created_at->isoFormat('D MMM YYYY'),
        ]);

        $resumen = [
            'trial'  => $tenants->where('plan', 'trial')->count(),
            'basico' => $tenants->where('plan', 'basico')->count(),
            'pro'    => $tenants->where('plan', 'pro')->count(),
            'mrr'    => $tenants->where('plan', 'basico')->count() * 299
                      + $tenants->where('plan', 'pro')->count() * 599,
        ];

        return Inertia::render('SuperAdmin/Saas', [
            'tenants' => $tenants->values(),
            'planes'  => self::PLANES,
            'resumen' => $resumen,
        ]);
    }

    public function actualizarPlan(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'plan'           => 'required|in:trial,basico,pro',
            'plan_expira_en' => 'nullable|date',
        ]);

        $planAnterior = $tenant->plan;
        $tenant->update($data);

        AuditLogger::log(
            'plan_cambio',
            "Plan de '{$tenant->nombre}' cambiado de '{$planAnterior}' a '{$data['plan']}'",
            $tenant->id,
            ['plan_anterior' => $planAnterior, 'plan_nuevo' => $data['plan']]
        );

        return back()->with('success', "Plan de {$tenant->nombre} actualizado a {$tenant->planLabel()}.");
    }
}
