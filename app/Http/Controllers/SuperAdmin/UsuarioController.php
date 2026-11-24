<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:200', 'rol' => 'nullable|in:admin,cajero,superadmin', 'estado' => 'nullable|in:habilitado,deshabilitado']);
        $query = User::with('tenant:id,nombre');
        $q = trim($filters['q'] ?? '');
        if ($q !== '') {
            $query->where(fn ($b) => $b->where('nombre', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%')->orWhere('apellido', 'like', '%'.$q.'%')->orWhere('username', 'like', '%'.$q.'%')->orWhereHas('tenant', fn ($t) => $t->where('nombre', 'like', '%'.$q.'%')));
        }
        if ($role = $filters['rol'] ?? null) {
            if ($role === 'superadmin') {
                $query->where('es_super_admin', true);
            } else {
                $query->where('rol', $role)->where('es_super_admin', false);
            }
        }
        if ($state = $filters['estado'] ?? null) {
            $query->where('activo', $state === 'habilitado');
        }
        $usuarios = $query->orderBy('tenant_id')->orderBy('created_at')->orderBy('id')->paginate(20)->withQueryString()
            ->through(fn (User $u) => [
                'id' => $u->id,
                'nombre' => trim("{$u->nombre} {$u->apellido}"),
                'username' => $u->username,
                'email' => $u->email,
                'rol' => $u->rol,
                'es_super_admin' => $u->es_super_admin,
                'activo' => $u->activo,
                'tenant_nombre' => $u->tenant?->nombre ?? '— Plataforma —',
                'creado_en' => $u->created_at->isoFormat('D MMM YYYY, HH:mm'),
                'creado_diff' => $u->created_at->diffForHumans(),
            ]);

        return Inertia::render('SuperAdmin/Usuarios', [
            'usuarios' => $usuarios,
            'filters' => $filters,
            'totalUsuarios' => User::count(),
            'totalActivos' => User::where('activo', true)->count(),
            'totalAdmins' => User::where('rol', 'admin')->where('es_super_admin', false)->count(),
        ]);
    }
}
