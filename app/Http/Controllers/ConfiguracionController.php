<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

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
        // PHP can reject the upload before Laravel's image/max rules run.
        $upload = $request->file('logo');
        if ($upload && ! $upload->isValid()) {
            $message = match ($upload->getError()) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El logotipo debe pesar como máximo 2 MB.',
                UPLOAD_ERR_PARTIAL => 'La carga del logotipo se interrumpió. Selecciona el archivo e inténtalo de nuevo.',
                default => 'El servidor no pudo recibir el logotipo. Inténtalo de nuevo.',
            };
            throw ValidationException::withMessages(['logo' => $message]);
        }

        $request->validate([
            'paleta'        => 'nullable|array',
            'logo'          => 'nullable|image|max:2048',
            'eliminar_logo' => 'nullable|boolean',
        ], [
            'logo.uploaded' => 'El servidor no pudo recibir el logotipo. Usa una imagen de hasta 2 MB e inténtalo de nuevo.',
            'logo.image' => 'Selecciona una imagen JPG, PNG, WebP, GIF o BMP válida.',
            'logo.max' => 'El logotipo debe pesar como máximo 2 MB.',
        ]);

        $tenant = auth()->user()->tenant;
        $path = null;
        if ($request->hasFile('logo')) {
            try {
                $path = $request->file('logo')->store("logos/{$tenant->id}", 'public');
            } catch (Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages(['logo' => 'No se pudo guardar el logotipo. El anterior se conserva. Inténtalo de nuevo.']);
            }
            if ($path === false) {
                throw ValidationException::withMessages(['logo' => 'No se pudo guardar el logotipo. El anterior se conserva. Inténtalo de nuevo.']);
            }
        }

        try {
            $previous = DB::transaction(function () use ($request, $tenant, $path) {
                $current = Tenant::query()->lockForUpdate()->findOrFail($tenant->id);
                $config = $current->configuracion ?? [];
                $previous = $config['logo'] ?? null;
                if ($request->has('paleta') && is_array($request->paleta)) {
                    $config['colores'] = array_intersect_key($request->paleta,
                        array_flip(['primario', 'medio', 'oscuro', 'hover', 'acento', 'texto', 'fondo']));
                }
                if ($path !== null || $request->boolean('eliminar_logo')) {
                    $config['logo'] = $path;
                }
                $current->update(['configuracion' => $config]);

                return $previous;
            });
        } catch (Throwable $exception) {
            $this->deleteLogoFile($tenant->id, $path);
            report($exception);
            throw ValidationException::withMessages([
                $path !== null || $request->boolean('eliminar_logo') ? 'logo' : 'paleta' =>
                    'No se pudo guardar la apariencia. Los cambios anteriores se conservan. Inténtalo de nuevo.',
            ]);
        }

        // Only retire the previous file after its replacement/removal is persisted.
        if ($path !== null || $request->boolean('eliminar_logo')) {
            $this->deleteLogoFile($tenant->id, $previous);
        }
        AuditLogger::log('config.apariencia', 'Apariencia actualizada', $tenant->id);

        return back()->with('success', $path !== null ? 'Apariencia y logotipo guardados correctamente.'
            : ($request->boolean('eliminar_logo') ? 'Logotipo eliminado correctamente.' : 'Apariencia guardada correctamente.'));
    }

    private function deleteLogoFile(int $tenantId, ?string $path): void
    {
        if (! $path || ! str_starts_with($path, "logos/{$tenantId}/") || str_contains($path, '..')) {
            return;
        }
        try {
            if (! Storage::disk('public')->delete($path)) {
                Log::warning('No se pudo retirar un logotipo anterior.', ['tenant_id' => $tenantId]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
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
