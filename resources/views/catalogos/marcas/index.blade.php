@extends('layouts.app')

@section('title', 'Marcas')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            🏭 Marcas
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Gestión de marcas de productos
        </p>
    </div>
    <a href="{{ route('catalogos.marcas.create') }}"
       style="background:#8B0000; color:white; padding:10px 22px; border-radius:8px;
              text-decoration:none; font-weight:600; font-size:0.9rem;">
        + Nueva Marca
    </a>
</div>

<div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#8B0000; color:white;">
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">#</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Nombre</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Estado</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($marcas as $marca)
            <tr style="border-bottom:1px solid #f0f0f0;">
                <td style="padding:14px 20px; color:#999; font-size:0.85rem;">
                    {{ $marca->id }}
                </td>
                <td style="padding:14px 20px; font-weight:500;">
                    {{ $marca->nombre }}
                </td>
                <td style="padding:14px 20px; text-align:center;">
                    @if($marca->activo)
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
                    <a href="{{ route('catalogos.marcas.edit', $marca) }}"
                       style="background:#f0a500; color:white; padding:6px 14px; border-radius:6px;
                              text-decoration:none; font-size:0.82rem; font-weight:600; margin-right:6px;">
                        Editar
                    </a>
                    <form method="POST"
                          action="{{ route('catalogos.marcas.destroy', $marca) }}"
                          style="display:inline;">
                        @csrf
                        @method('DELETE')
                        @if($marca->activo)
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
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding:40px; text-align:center; color:#bbb;">
                    No hay marcas registradas.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection