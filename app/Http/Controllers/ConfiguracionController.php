<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $tenant = $user->tenant;
        $config = $tenant?->configuracion ?? [];

        return Inertia::render('Configuracion/Index', [
            'configuracion' => $config,
            'logoUrl'       => !empty($config['logo'])
                ? Storage::disk('public')->url($config['logo'])
                : null,
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

}
