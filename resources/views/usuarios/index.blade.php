@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            👥 Usuarios
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Gestión de usuarios del sistema
        </p>
    </div>
    <a href="{{ route('usuarios.create') }}"
       style="background:#8B0000; color:white; padding:10px 22px; border-radius:8px;
              text-decoration:none; font-weight:600; font-size:0.9rem;">
        + Nuevo Usuario
    </a>
</div>

<div style="background:#fff; border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#8B0000; color:white;">
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">#</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Nombre</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Usuario</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Email</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Rol</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Estado</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
            <tr style="border-bottom:1px solid #f0f0f0;
                       {{ !$usuario->activo ? 'opacity:0.5;' : '' }}">
                <td style="padding:14px 20px; color:#999; font-size:0.85rem;">
                    {{ $usuario->id }}
                </td>
                <td style="padding:14px 20px;">
                    <span style="font-weight:600; color:#333;">
                        {{ $usuario->nombre }} {{ $usuario->apellido }}
                    </span>
                    @if($usuario->id === auth()->id())
                        <span style="background:#e8f4fd; color:#0c5460; padding:2px 8px;
                                     border-radius:20px; font-size:0.75rem; font-weight:600;
                                     margin-left:8px;">
                            Tú
                        </span>
                    @endif
                </td>
                <td style="padding:14px 20px; color:#666; font-size:0.9rem;">
                    {{ $usuario->username }}
                </td>
                <td style="padding:14px 20px; color:#666; font-size:0.9rem;">
                    {{ $usuario->email }}
                </td>
                <td style="padding:14px 20px; text-align:center;">
                    @if($usuario->rol === 'admin')
                        <span style="background:#fff3cd; color:#856404; padding:4px 12px;
                                     border-radius:20px; font-size:0.8rem; font-weight:600;">
                            Admin
                        </span>
                    @else
                        <span style="background:#e2e3e5; color:#383d41; padding:4px 12px;
                                     border-radius:20px; font-size:0.8rem; font-weight:600;">
                            Cajero
                        </span>
                    @endif
                </td>
                <td style="padding:14px 20px; text-align:center;">
                    @if($usuario->activo)
                        <span style="background:#d4edda; color:#155724; padding:4px 12px;
                                     border-radius:20px; font-size:0.8rem; font-weight:600;">
                            Activo
                        </span>
                    @else
                        <span style="background:#f8d7da; color:#721c24; padding:4px 12px;
                                     border-radius:20px; font-size:0.8rem; font-weight:600;">
                            Inactivo
                        </span>
                    @endif
                </td>
                <td style="padding:14px 20px; text-align:center;">
                    <a href="{{ route('usuarios.edit', $usuario) }}"
                       style="background:#f0a500; color:white; padding:6px 14px;
                              border-radius:6px; text-decoration:none;
                              font-size:0.82rem; font-weight:600; margin-right:6px;">
                        Editar
                    </a>
                    @if($usuario->id !== auth()->id())
                        <form method="POST"
                              action="{{ route('usuarios.destroy', $usuario) }}"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')
                            @if($usuario->activo)
                                <button type="submit"
                                        style="background:#dc3545; color:white; padding:6px 14px;
                                               border-radius:6px; border:none; font-size:0.82rem;
                                               font-weight:600; cursor:pointer;">
                                    Desactivar
                                </button>
                            @else
                                <button type="submit"
                                        style="background:#28a745; color:white; padding:6px 14px;
                                               border-radius:6px; border:none; font-size:0.82rem;
                                               font-weight:600; cursor:pointer;">
                                    Activar
                                </button>
                            @endif
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:40px; text-align:center; color:#bbb;">
                    No hay usuarios registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection