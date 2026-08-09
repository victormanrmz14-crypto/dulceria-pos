<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user()?->es_super_admin) {
            abort(403, 'Acceso exclusivo para administradores de plataforma.');
        }

        return $next($request);
    }
}
