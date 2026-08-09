<?php

namespace App\Http\Middleware;

use App\Models\Anuncio;
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
                return [
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
