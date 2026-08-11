<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user         = auth()->user();
        $impersonando = session()->has('superadmin_original_id');

        // Un super admin no opera dentro de los módulos de una dulcería (no tiene
        // tenant): se le regresa a su panel. Para actuar como una dulcería debe
        // impersonar — y durante la impersonación el usuario autenticado es el
        // admin del tenant (es_super_admin = false), así que no entra aquí.
        if ($user->es_super_admin && ! $impersonando) {
            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::location(route('superadmin.dashboard'));
            }
            return redirect()->route('superadmin.dashboard');
        }

        // Tenant desactivado — bloquear acceso (excepto durante impersonación de super admin)
        if (! $impersonando && $user->tenant_id && ! $user->tenant?->activo) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::location(route('login'));
            }
            return redirect()->route('login')->withErrors([
                'email' => 'Tu dulcería ha sido desactivada. Contacta a soporte.',
            ]);
        }

        // Usuario desactivado individualmente
        if (! $user->activo) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::location(route('login'));
            }
            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ]);
        }

        return $next($request);
    }
}
