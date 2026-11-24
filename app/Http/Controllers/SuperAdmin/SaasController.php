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
        'trial' => ['label' => 'Trial',   'precio' => 0,   'max_usuarios' => 2,  'max_productos' => 50,  'ventas' => '100/mes'],
        'basico' => ['label' => 'Básico',  'precio' => 299, 'max_usuarios' => 5,  'max_productos' => 200, 'ventas' => 'Ilimitadas'],
        'pro' => ['label' => 'Pro',     'precio' => 599, 'max_usuarios' => 999, 'max_productos' => 999, 'ventas' => 'Ilimitadas'],
    ];

    public function index(): Response
    {
        $tenants = Tenant::withCount('users')->latest()->paginate(20)->withQueryString()->through(fn (Tenant $t) => [
            'id' => $t->id, 'nombre' => $t->nombre, 'activo' => $t->activo,
            'plan' => $t->plan, 'plan_label' => $t->planLabel(),
            'plan_expira_en' => $t->plan_expira_en?->isoFormat('D MMM YYYY'),
            'plan_expira_input' => $t->plan_expira_en?->format('Y-m-d'),
            'plan_vencido' => $t->plan_expira_en && $t->plan_expira_en->lte(now()),
            'dias_restantes' => $t->diasRestantesTrial(), 'usuarios' => $t->users_count,
            'creado_en' => $t->created_at->isoFormat('D MMM YYYY'),
        ]);
        $counts = Tenant::selectRaw('plan, COUNT(*) as total')->groupBy('plan')->pluck('total', 'plan');
        $resumen = ['trial' => (int) ($counts['trial'] ?? 0), 'basico' => (int) ($counts['basico'] ?? 0), 'pro' => (int) ($counts['pro'] ?? 0),
            'vencidos' => Tenant::where('plan_expira_en', '<=', now())->count()];

        return Inertia::render('SuperAdmin/Saas', ['tenants' => $tenants, 'planes' => self::PLANES, 'resumen' => $resumen]);
    }

    public function actualizarPlan(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'plan' => 'required|in:trial,basico,pro',
            'plan_expira_en' => 'nullable|date',
        ], [
            'plan.required' => 'Selecciona un plan.',
            'plan.in' => 'Selecciona uno de los planes disponibles.',
            'plan_expira_en.date' => 'Escribe una fecha de expiración válida.',
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
