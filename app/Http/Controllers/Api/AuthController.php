<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credenciales = filter_var($request->email, FILTER_VALIDATE_EMAIL)
            ? ['email'    => $request->email,    'password' => $request->password]
            : ['username' => $request->email,    'password' => $request->password];

        if (!Auth::attempt($credenciales)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $user = Auth::user();

        if (!$user->activo) {
            Auth::logout();
            return response()->json(['message' => 'Cuenta desactivada.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        AuditLogger::log('api_login', 'Inicio de sesión vía API', $user->tenant_id);

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'             => $user->id,
                'nombre'         => $user->nombre,
                'email'          => $user->email,
                'rol'            => $user->rol,
                'es_super_admin' => $user->es_super_admin,
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        AuditLogger::log('api_logout', 'Cierre de sesión vía API', $request->user()->tenant_id);

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.'], 200);
    }
}
