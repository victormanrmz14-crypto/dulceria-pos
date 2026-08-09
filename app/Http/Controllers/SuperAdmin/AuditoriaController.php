<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use Inertia\Inertia;
use Inertia\Response;

class AuditoriaController extends Controller
{
    public function index(): Response
    {
        $logs = AuditLog::with(['usuario', 'tenant'])
            ->latest()
            ->limit(200)
            ->get()
            ->map(fn (AuditLog $l) => [
                'id'          => $l->id,
                'accion'      => $l->accion,
                'descripcion' => $l->descripcion,
                'ip'          => $l->ip,
                'meta'        => $l->meta,
                'usuario'     => $l->usuario?->email ?? '—',
                'tenant'      => $l->tenant?->nombre ?? '— Plataforma —',
                'fecha'       => $l->created_at->isoFormat('D MMM YYYY, HH:mm:ss'),
                'diff'        => $l->created_at->diffForHumans(),
            ]);

        $tenants = Tenant::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('SuperAdmin/Auditoria', [
            'logs'    => $logs->values(),
            'tenants' => $tenants->values(),
        ]);
    }
}
