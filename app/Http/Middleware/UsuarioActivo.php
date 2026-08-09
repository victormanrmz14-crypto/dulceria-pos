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
