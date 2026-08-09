<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $tenant = $user->tenant;
        $config = $tenant?->configuracion ?? [];

        $usuarios = $tenant
            ? $tenant->users()
                ->select(['id', 'nombre', 'apellido', 'email', 'username', 'rol', 'activo', 'created_at'])
                ->orderBy('rol')
                ->orderBy('nombre')
                ->get()
                ->map(fn ($u) => [
                    'id'       => $u->id,
                    'nombre'   => trim("{$u->nombre} {$u->apellido}"),
                    'email'    => $u->email,
                    'username' => $u->username,
                    'rol'      => $u->rol,
                    'activo'   => (bool) $u->activo,
                    'desde'    => $u->created_at->diffForHumans(),
                ])
            : [];

        return Inertia::render('Configuracion/Index', [
            'configuracion' => $config,
            'usuarios'      => $usuarios,
        ]);
    }

    public function guardarApariencia(Request $request)
    {
        $request->validate([
            'paleta'        => 'nullable|array',
            'logo'          => 'nullable|image|max:2048',
            'eliminar_logo' => 'nullable|boolean',
        ]);

        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        if ($request->has('paleta') && is_array($request->paleta)) {
            $config['colores'] = array_intersect_key(
                $request->paleta,
                array_flip(['primario', 'medio', 'oscuro', 'hover', 'acento', 'texto', 'fondo'])
            );
        }

        if ($request->boolean('eliminar_logo') && !empty($config['logo'])) {
            Storage::disk('public')->delete($config['logo']);
            $config['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if (!empty($config['logo'])) {
                Storage::disk('public')->delete($config['logo']);
            }
            $config['logo'] = $request->file('logo')->store("logos/{$tenant->id}", 'public');
        }

        $tenant->update(['configuracion' => $config]);
        AuditLogger::log('config.apariencia', 'Apariencia actualizada', $tenant->id);

        return back()->with('success', 'Apariencia guardada correctamente.');
    }

    public function guardarNegocio(Request $request)
    {
        $request->validate([
            'nombre_mostrar' => 'nullable|string|max:100',
            'direccion'      => 'nullable|string|max:250',
            'telefono'       => 'nullable|string|max:30',
            'rfc'            => 'nullable|string|max:20',
            'email_negocio'  => 'nullable|email|max:100',
        ]);

        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];
        $config['negocio'] = [
            'nombre_mostrar' => $request->nombre_mostrar,
            'direccion'      => $request->direccion,
            'telefono'       => $request->telefono,
            'rfc'            => $request->rfc,
            'email'          => $request->email_negocio,
        ];

        $tenant->update(['configuracion' => $config]);
        AuditLogger::log('config.negocio', 'Información del negocio actualizada', $tenant->id);

        return back()->with('success', 'Información del negocio guardada.');
    }

    public function guardarTickets(Request $request)
    {
        $request->validate([
            'encabezado'  => 'nullable|string|max:400',
            'pie'         => 'nullable|string|max:400',
            'mostrar_logo'=> 'nullable|boolean',
            'mostrar_rfc' => 'nullable|boolean',
        ]);

        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];
        $config['ticket'] = [
            'encabezado'  => $request->encabezado ?? '',
            'pie'         => $request->pie ?? '',
            'mostrar_logo'=> $request->boolean('mostrar_logo', true),
            'mostrar_rfc' => $request->boolean('mostrar_rfc', true),
        ];

        $tenant->update(['configuracion' => $config]);
        AuditLogger::log('config.tickets', 'Configuración de recibos actualizada', $tenant->id);

        return back()->with('success', 'Configuración de recibos guardada.');
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual'       => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        AuditLogger::log('config.password', 'Contraseña cambiada por el usuario', $user->tenant_id);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
