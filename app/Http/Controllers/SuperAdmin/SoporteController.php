<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SoporteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Soporte');
    }

    public function resetPassword(Request $request, User $usuario): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $usuario->update(['password' => Hash::make($request->password)]);

        AuditLogger::log(
            'reset_password',
            "Contraseña reseteada para '{$usuario->email}'",
            $usuario->tenant_id,
            ['user_id' => $usuario->id]
        );

        return back()->with('success', "Contraseña de {$usuario->email} actualizada.");
    }

    public function reenviarBienvenida(Request $request, User $usuario): RedirectResponse
    {
        $password = Str::password(12);
        $usuario->update(['password' => Hash::make($password)]);

        try {
            \Illuminate\Support\Facades\Mail::to($usuario->email)
                ->send(new \App\Mail\BienvenidaSoporte($usuario, $password));
            $msg = "Correo de bienvenida enviado a {$usuario->email}.";
        } catch (\Throwable) {
            $msg = "Contraseña reseteada a '{$password}'. El correo no pudo enviarse (revisar config de mail).";
        }

        AuditLogger::log('reenvio_bienvenida', "Bienvenida reenviada a '{$usuario->email}'", $usuario->tenant_id);
        return back()->with('success', $msg);
    }
}
