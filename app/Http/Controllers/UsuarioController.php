<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('nombre')->get()->map(fn ($u) => [
            'id'       => $u->id,
            'nombre'   => $u->nombre,
            'apellido' => $u->apellido,
            'username' => $u->username,
            'email'    => $u->email,
            'rol'      => $u->rol,
            'activo'   => (bool) $u->activo,
        ])->values();

        return \Inertia\Inertia::render('Usuarios/Index', ['usuarios' => $usuarios]);
    }

    public function create()
    {
        return \Inertia\Inertia::render('Usuarios/Create');
    }

    public function store(StoreUsuarioRequest $request)
    {
        User::create([
            'nombre'   => $request->nombre,
            'apellido' => $request->apellido,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => $request->rol,
            'activo'   => true,
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return \Inertia\Inertia::render('Usuarios/Edit', [
            'usuario' => [
                'id'       => $usuario->id,
                'nombre'   => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'username' => $usuario->username,
                'email'    => $usuario->email,
                'rol'      => $usuario->rol,
            ],
        ]);
    }

    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
        // No permitir que un admin se quite su propio rol (podría dejar
        // el sistema sin administradores)
        if ($usuario->id === auth()->id() && $request->rol !== 'admin') {
            return back()->withInput()
                ->with('error', 'No puedes quitarte tu propio rol de administrador.');
        }

        $datos = [
            'nombre'   => $request->nombre,
            'apellido' => $request->apellido,
            'username' => $request->username,
            'email'    => $request->email,
            'rol'      => $request->rol,
        ];

        // Solo actualizar contraseña si se proporcionó
        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        // No permitir desactivarse a sí mismo
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $mensaje = $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.';

        return redirect()->route('usuarios.index')
            ->with('success', $mensaje);
    }
}