<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->rol !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}