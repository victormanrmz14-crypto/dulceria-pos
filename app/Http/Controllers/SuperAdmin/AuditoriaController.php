<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate(['q' => 'nullable|string|max:200', 'accion' => 'nullable|string|max:100']);
        $query = AuditLog::with(['usuario', 'tenant']);
        if ($action = $filters['accion'] ?? null) {
            $query->where('accion', $action);
        }
        $q = trim($filters['q'] ?? '');
        if ($q !== '') {
            $query->where(fn ($b) => $b->where('descripcion', 'like', '%'.$q.'%')->orWhere('accion', 'like', '%'.$q.'%')->orWhereHas('usuario', fn ($u) => $u->where('email', 'like', '%'.$q.'%'))->orWhereHas('tenant', fn ($t) => $t->where('nombre', 'like', '%'.$q.'%')));
        }
        $logs = $query->latest()->orderByDesc('id')->paginate(25)->withQueryString()
            ->through(fn (AuditLog $l) => [
                'id' => $l->id,
                'accion' => $l->accion,
                'descripcion' => $l->descripcion,
                'ip' => $l->ip,
                'meta' => $l->meta,
                'usuario' => $l->usuario?->email ?? '—',
                'tenant' => $l->tenant?->nombre ?? '— Plataforma —',
                'fecha' => $l->created_at->isoFormat('D MMM YYYY, HH:mm:ss'),
                'diff' => $l->created_at->diffForHumans(),
            ]);

        return Inertia::render('SuperAdmin/Auditoria', [
            'logs' => $logs, 'filters' => $filters,
            'acciones' => AuditLog::distinct()->orderBy('accion')->pluck('accion'),
        ]);
    }
}
