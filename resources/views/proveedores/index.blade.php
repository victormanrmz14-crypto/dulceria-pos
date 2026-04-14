@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            Proveedores
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Gestiona los proveedores de productos.
        </p>
    </div>
    <a href="{{ route('catalogos.proveedores.create') }}"
       style="background:#8B0000; color:#fff; padding:10px 20px; border-radius:8px;
              text-decoration:none; font-weight:600; font-size:0.9rem;">
        + Nuevo Proveedor
    </a>
</div>

<div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#8B0000; color:#fff;">
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem; font-weight:600;">Nombre</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem; font-weight:600;">Correo</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem; font-weight:600;">Teléfono</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem; font-weight:600;">Estado</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem; font-weight:600;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proveedores as $proveedor)
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:14px 20px; font-size:0.9rem; font-weight:500;">
                        {{ $proveedor->nombre }}
                    </td>
                    <td style="padding:14px 20px; font-size:0.9rem; color:#555;">
                        {{ $proveedor->email }}
                    </td>
                    <td style="padding:14px 20px; font-size:0.9rem; color:#555;">
                        {{ $proveedor->telefono ?? '—' }}
                    </td>
                    <td style="padding:14px 20px; text-align:center;">
                        @if($proveedor->activo)
                            <span style="background:#d4edda; color:#155724; padding:3px 10px;
                                         border-radius:20px; font-size:0.78rem; font-weight:600;">
                                Activo
                            </span>
                        @else
                            <span style="background:#f8d7da; color:#721c24; padding:3px 10px;
                                         border-radius:20px; font-size:0.78rem; font-weight:600;">
                                Inactivo
                            </span>
                        @endif
                    </td>
                    <td style="padding:14px 20px; text-align:center;">
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <a href="{{ route('catalogos.proveedores.edit', $proveedor) }}"
                               style="background:#f0f0f0; color:#333; padding:6px 14px; border-radius:6px;
                                      text-decoration:none; font-size:0.82rem; font-weight:500;">
                                Editar
                            </a>
                            <form method="POST"
                                  action="{{ route('catalogos.proveedores.destroy', $proveedor) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="background:{{ $proveedor->activo ? '#fff5f5' : '#f0fff4' }};
                                               color:{{ $proveedor->activo ? '#c0392b' : '#27ae60' }};
                                               border:none; padding:6px 14px; border-radius:6px;
                                               font-size:0.82rem; font-weight:500; cursor:pointer;">
                                    {{ $proveedor->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:40px; text-align:center; color:#aaa; font-size:0.9rem;">
                        No hay proveedores registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
