@extends('layouts.app')

@section('title', 'Editar Categoría')

@section('content')

<div style="max-width:500px;">
    <div style="margin-bottom:28px;">
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            Editar Categoría
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Modifica el nombre de la categoría.
        </p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:32px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <form method="POST" action="{{ route('catalogos.categorias.update', $categoria) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Nombre de la categoría
                </label>
                <input type="text" name="nombre"
                       value="{{ old('nombre', $categoria->nombre) }}"
                       style="width:100%; padding:10px 14px; border:1px solid #ddd;
                              border-radius:8px; font-size:0.95rem; outline:none;"
                       required>
                @error('nombre')
                    <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div style="display:flex; gap:12px; margin-top:28px;">
                <button type="submit"
                        style="background:#8B0000; color:white; padding:10px 24px;
                               border:none; border-radius:8px; font-weight:600;
                               font-size:0.9rem; cursor:pointer;">
                    Actualizar
                </button>
                <a href="{{ route('catalogos.categorias.index') }}"
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