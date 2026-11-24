<?php

namespace App\Http\Middleware;

use App\Models\Anuncio;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function handle(Request $request, Closure $next)
    {
        $response = parent::handle($request, $next);
        // Private tenant pages must be revalidated after a native logout/back.
        if ($request->user() && ! $request->user()->es_super_admin) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'nombre' => $request->user()->nombre,
                    'apellido' => $request->user()->apellido,
                    'rol' => $request->user()->rol,
                    'es_super_admin' => $request->user()->es_super_admin,
                    'avatar_url' => $request->user()->avatarUrl(),
                ] : null,
            ],

            'flash' => [
                'success'    => $request->session()->get('success'),
                'error'      => $request->session()->get('error'),
                'bienvenida' => (bool) $request->session()->get('bienvenida'),
            ],

            'impersonando' => $request->session()->has('superadmin_original_id'),

            'anuncios' => $request->user()
                ? Anuncio::vigentes()->latest()->get()->map(fn ($a) => [
                    'id'    => $a->id,
                    'titulo'=> $a->titulo,
                    'cuerpo'=> $a->cuerpo,
                    'tipo'  => $a->tipo,
                ])->values()
                : [],

            'csrf' => csrf_token(),

            'tenantConfig' => function () use ($request) {
                $user = $request->user();
                if (!$user || $user->es_super_admin) return null;
                $tenant = $user->tenant;
                if (!$tenant) return null;
                $config = $tenant->configuracion ?? [];
                $commercialName = trim($config['negocio']['nombre_mostrar'] ?? '');
                $tenantName = trim($tenant->nombre ?? '');
                return [
                    // Navigation identity: commercial name, registered tenant name, platform.
                    // Resolve from this request's user; never cache across sessions.
                    'nombre'        => $commercialName !== '' ? $commercialName
                        : ($tenantName !== '' ? $tenantName : 'Dulcería POS'),
                    'colores'       => $config['colores'] ?? null,
                    'logo'          => !empty($config['logo'])
                        ? Storage::disk('public')->url($config['logo'])
                        : null,
                    'nombre_mostrar'=> $config['negocio']['nombre_mostrar'] ?? null,
                    'ticket'        => $config['ticket'] ?? null,
                ];
            },
        ];
    }
}
