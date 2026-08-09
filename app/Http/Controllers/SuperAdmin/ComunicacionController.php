<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComunicacionController extends Controller
{
    public function index(): Response
    {
        $anuncios = Anuncio::with('autor')->latest()->get()->map(fn (Anuncio $a) => [
            'id'         => $a->id,
            'titulo'     => $a->titulo,
            'cuerpo'     => $a->cuerpo,
            'tipo'       => $a->tipo,
            'activo'     => $a->activo,
            'expira_en'  => $a->expira_en?->isoFormat('D MMM YYYY'),
            'vigente'    => $a->activo && (! $a->expira_en || $a->expira_en->gt(now())),
            'autor'      => $a->autor?->nombre ?? '—',
            'creado_en'  => $a->created_at->isoFormat('D MMM YYYY, HH:mm'),
        ]);

        return Inertia::render('SuperAdmin/Comunicacion', [
            'anuncios' => $anuncios->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo'    => 'required|string|max:200',
            'cuerpo'    => 'required|string|max:2000',
            'tipo'      => 'required|in:info,aviso,alerta',
            'expira_en' => 'nullable|date|after:now',
        ]);

        $anuncio = Anuncio::create([...$data, 'activo' => true, 'creado_por' => auth()->id()]);
        AuditLogger::log('anuncio_creado', "Anuncio creado: '{$anuncio->titulo}'");

        return back()->with('success', 'Anuncio publicado correctamente.');
    }

    public function toggle(Anuncio $anuncio): RedirectResponse
    {
        $anuncio->update(['activo' => ! $anuncio->activo]);
        $estado = $anuncio->activo ? 'activado' : 'desactivado';
        AuditLogger::log('anuncio_toggle', "Anuncio '{$anuncio->titulo}' {$estado}");
        return back()->with('success', "Anuncio {$estado}.");
    }

    public function destroy(Anuncio $anuncio): RedirectResponse
    {
        AuditLogger::log('anuncio_eliminado', "Anuncio eliminado: '{$anuncio->titulo}'");
        $anuncio->delete();
        return back()->with('success', 'Anuncio eliminado.');
    }

    public function emailMasivo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asunto'  => 'required|string|max:200',
            'mensaje' => 'required|string|max:5000',
        ]);

        $admins    = User::where('rol', 'admin')->whereNotNull('tenant_id')->where('activo', true)->get();
        $enviados  = 0;
        $fallidos  = 0;

        foreach ($admins as $admin) {
            try {
                \Illuminate\Support\Facades\Mail::to($admin->email)
                    ->send(new \App\Mail\EmailMasivo($admin, $data['asunto'], $data['mensaje']));
                $enviados++;
            } catch (\Throwable) {
                $fallidos++;
            }
        }

        AuditLogger::log('email_masivo', "Email masivo enviado: '{$data['asunto']}' — {$enviados} OK, {$fallidos} fallidos");
        $msg = "Email enviado a {$enviados} administrador(es).";
        if ($fallidos > 0) $msg .= " {$fallidos} fallidos (revisar config de mail).";

        return back()->with('success', $msg);
    }
}
