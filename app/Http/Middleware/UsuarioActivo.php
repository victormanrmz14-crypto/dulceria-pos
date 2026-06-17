<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsuarioActivo
{
    /**
     * Cierra la sesión de usuarios desactivados en cada request,
     * no solo al iniciar sesión.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->activo) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // En requests Inertia (XHR) hay que forzar recarga completa porque
            // el login es una página Blade, no un componente Inertia
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
