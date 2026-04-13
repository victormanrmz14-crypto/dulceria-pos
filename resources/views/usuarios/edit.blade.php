@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')

<div style="max-width:600px;">
    <div style="margin-bottom:28px;">
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            Editar Usuario
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Modifica los datos del usuario. Deja la contraseña vacía para no cambiarla.
        </p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:32px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')

            {{-- Nombre y Apellido --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Nombre <span style="color:#dc3545;">*</span>
                    </label>
                    <input type="text" name="nombre"
                           value="{{ old('nombre', $usuario->nombre) }}"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;">
                    @error('nombre')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Apellido
                    </label>
                    <input type="text" name="apellido"
                           value="{{ old('apellido', $usuario->apellido) }}"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;">
                </div>
            </div>

            {{-- Username y Rol --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Usuario <span style="color:#dc3545;">*</span>
                    </label>
                    <input type="text" name="username"
                           value="{{ old('username', $usuario->username) }}"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;">
                    @error('username')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Rol <span style="color:#dc3545;">*</span>
                    </label>
                    <select name="rol"
                            style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                   border-radius:8px; font-size:0.95rem; outline:none; background:#fff;">
                        <option value="admin"  {{ old('rol', $usuario->rol) == 'admin'  ? 'selected' : '' }}>Administrador</option>
                        <option value="cajero" {{ old('rol', $usuario->rol) == 'cajero' ? 'selected' : '' }}>Cajero</option>
                    </select>
                    @error('rol')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Email --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Email <span style="color:#dc3545;">*</span>
                </label>
                <input type="email" name="email"
                       value="{{ old('email', $usuario->email) }}"
                       style="width:100%; padding:10px 14px; border:1px solid #ddd;
                              border-radius:8px; font-size:0.95rem; outline:none;">
                @error('email')
                    <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password opcional --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:28px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Nueva contraseña
                    </label>
                    <input type="password" name="password"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;"
                           placeholder="Dejar vacío para no cambiar">
                    @error('password')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Confirmar contraseña
                    </label>
                    <input type="password" name="password_confirmation"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;">
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="background:#8B0000; color:white; padding:10px 24px;
                               border:none; border-radius:8px; font-weight:600;
                               font-size:0.9rem; cursor:pointer;">
                    Actualizar Usuario
                </button>
                <a href="{{ route('usuarios.index') }}"
                   style="background:#f0f0f0; color:#555; padding:10px 24px;
                          border-radius:8px; text-decoration:none; font-weight:600;
                          font-size:0.9rem;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection