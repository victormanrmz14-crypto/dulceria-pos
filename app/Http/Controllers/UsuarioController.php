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
        $usuarios = User::orderBy('nombre')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
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
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
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