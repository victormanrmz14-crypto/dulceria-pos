<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\SuperAdmin\Analytics;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function index(Request $request, Analytics $analytics)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:200', 'estado' => 'nullable|in:habilitadas,deshabilitadas,atencion,ok', 'actividad' => 'nullable|in:reciente,sin_recientes,nunca']);
        $query = $analytics->tenants();
        $q = trim($filters['q'] ?? '');
        if ($q !== '') {
            $query->where(fn ($b) => $b->where('nombre', 'like', '%'.$q.'%')->orWhereHas('users', fn ($u) => $u->where('rol', 'admin')->where(fn ($u) => $u->where('email', 'like', '%'.$q.'%')->orWhere('nombre', 'like', '%'.$q.'%')->orWhere('apellido', 'like', '%'.$q.'%'))));
        }
        $state = $filters['estado'] ?? '';
        if ($state === 'habilitadas' || $state === 'deshabilitadas') {
            $query->where('activo', $state === 'habilitadas');
        }
        if ($state === 'atencion') {
            $analytics->attention($query);
        }
        if ($state === 'ok') {
            $query->whereNotIn('id', $analytics->attention(Tenant::query())->select('id'));
        }
        $activity = $filters['actividad'] ?? '';
        if ($activity === 'nunca') {
            $query->whereNotIn('id', $analytics->sales()->select('tenant_id'));
        }
        if ($activity === 'reciente') {
            $query->whereIn('id', $analytics->sales()->select('tenant_id')->where('created_at', '>=', $analytics->start));
        }
        if ($activity === 'sin_recientes') {
            $query->whereIn('id', $analytics->sales()->select('tenant_id'))->whereNotIn('id', $analytics->sales()->select('tenant_id')->where('created_at', '>=', $analytics->start));
        }

        return Inertia::render('SuperAdmin/Dulcerias', [
            'tenants' => $query->latest()->orderByDesc('id')->paginate(20)->withQueryString()->through(fn ($t) => $analytics->row($t)),
            'filters' => $filters, 'periodo' => $analytics->period(),
            'total' => Tenant::count(), 'habilitadas' => Tenant::where('activo', true)->count(),
            'conProblemas' => $analytics->attention(Tenant::query())->count(),
        ]);
    }
}
