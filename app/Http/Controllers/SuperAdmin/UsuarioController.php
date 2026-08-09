<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('tenant')
            ->orderBy('tenant_id')
            ->orderBy('created_at')
            ->get()
            ->map(fn (User $u) => [
                'id'            => $u->id,
                'nombre'        => trim("{$u->nombre} {$u->apellido}"),
                'username'      => $u->username,
                'email'         => $u->email,
                'rol'           => $u->rol,
                'es_super_admin'=> $u->es_super_admin,
                'activo'        => $u->activo,
                'tenant_nombre' => $u->tenant?->nombre ?? '— Plataforma —',
                'creado_en'     => $u->created_at->isoFormat('D MMM YYYY, HH:mm'),
                'creado_diff'   => $u->created_at->diffForHumans(),
            ]);

        return Inertia::render('SuperAdmin/Usuarios', [
            'usuarios'      => $usuarios->values(),
            'totalUsuarios' => $usuarios->count(),
            'totalActivos'  => $usuarios->where('activo', true)->count(),
            'totalAdmins'   => $usuarios->where('rol', 'admin')->count(),
        ]);
    }
}
