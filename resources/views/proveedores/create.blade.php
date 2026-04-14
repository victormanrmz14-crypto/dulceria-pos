@extends('layouts.app')

@section('title', 'Nuevo Proveedor')

@section('content')

<div style="max-width:640px;">
    <div style="margin-bottom:28px;">
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            Nuevo Proveedor
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Completa el formulario para registrar un proveedor.
        </p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:32px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <form method="POST" action="{{ route('catalogos.proveedores.store') }}">
            @csrf

            {{-- Nombre --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Nombre <span style="color:#dc3545;">*</span>
                </label>
                <input type="text" name="nombre" value="{{ old('nombre') }}"
                       style="width:100%; padding:10px 14px; border:1px solid #ddd;
                              border-radius:8px; font-size:0.95rem; outline:none;"
                       placeholder="Nombre del proveedor">
                @error('nombre')
                    <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Correo electrónico <span style="color:#dc3545;">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                       style="width:100%; padding:10px 14px; border:1px solid #ddd;
                              border-radius:8px; font-size:0.95rem; outline:none;"
                       placeholder="proveedor@ejemplo.com">
                @error('email')
                    <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Teléfono --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Teléfono
                </label>
                <input type="text" name="telefono" value="{{ old('telefono') }}"
                       style="width:100%; padding:10px 14px; border:1px solid #ddd;
                              border-radius:8px; font-size:0.95rem; outline:none;"
                       placeholder="Ej. 55 1234 5678">
                @error('telefono')
                    <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notas --}}
            <div style="margin-bottom:28px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Notas
                </label>
                <textarea name="notas" rows="3"
                          style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                 border-radius:8px; font-size:0.95rem; outline:none;
                                 resize:vertical;"
                          placeholder="Información adicional del proveedor...">{{ old('notas') }}</textarea>
                @error('notas')
                    <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="background:#8B0000; color:white; padding:10px 24px;
                               border:none; border-radius:8px; font-weight:600;
                               font-size:0.9rem; cursor:pointer;">
                    Guardar Proveedor
                </button>
                <a href="{{ route('catalogos.proveedores.index') }}"
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
