@extends('layouts.app')

@section('title', 'Nuevo Producto')

@section('content')

<div style="max-width:680px;">
    <div style="margin-bottom:28px;">
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            Nuevo Producto
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Completa el formulario para agregar un producto.
        </p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:32px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <form method="POST" action="{{ route('productos.store') }}">
            @csrf

            {{-- Nombre --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Nombre del producto <span style="color:#dc3545;">*</span>
                </label>
                <input type="text" name="nombre" value="{{ old('nombre') }}"
                       style="width:100%; padding:10px 14px; border:1px solid #ddd;
                              border-radius:8px; font-size:0.95rem; outline:none;"
                       placeholder="Ej. Gomitas de fresa">
                @error('nombre')
                    <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Categoría y Marca --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Categoría <span style="color:#dc3545;">*</span>
                    </label>
                    <select name="categoria_id"
                            style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                   border-radius:8px; font-size:0.95rem; outline:none; background:#fff;">
                        <option value="">Selecciona...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Marca <span style="color:#dc3545;">*</span>
                    </label>
                    <select name="marca_id"
                            style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                   border-radius:8px; font-size:0.95rem; outline:none; background:#fff;">
                        <option value="">Selecciona...</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}"
                                {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                                {{ $marca->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('marca_id')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Precio y Unidad --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Precio <span style="color:#dc3545;">*</span>
                    </label>
                    <input type="number" name="precio" value="{{ old('precio') }}"
                           step="0.01" min="0"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;"
                           placeholder="0.00">
                    @error('precio')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Unidad de medida <span style="color:#dc3545;">*</span>
                    </label>
                    <select name="unidad_medida"
                            style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                   border-radius:8px; font-size:0.95rem; outline:none; background:#fff;">
                        <option value="">Selecciona...</option>
                        <option value="pieza"  {{ old('unidad_medida') == 'pieza'  ? 'selected' : '' }}>Pieza</option>
                        <option value="kg"     {{ old('unidad_medida') == 'kg'     ? 'selected' : '' }}>Kilogramo (kg)</option>
                        <option value="gramo"  {{ old('unidad_medida') == 'gramo'  ? 'selected' : '' }}>Gramo</option>
                        <option value="litro"  {{ old('unidad_medida') == 'litro'  ? 'selected' : '' }}>Litro</option>
                        <option value="caja"   {{ old('unidad_medida') == 'caja'   ? 'selected' : '' }}>Caja</option>
                        <option value="bolsa"  {{ old('unidad_medida') == 'bolsa'  ? 'selected' : '' }}>Bolsa</option>
                        <option value="paquete"{{ old('unidad_medida') == 'paquete'? 'selected' : '' }}>Paquete</option>
                    </select>
                    @error('unidad_medida')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Stock y Stock mínimo --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:28px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Stock inicial <span style="color:#dc3545;">*</span>
                    </label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}"
                           step="0.001" min="0"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;">
                    @error('stock')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;
                                  font-size:0.9rem; color:#555;">
                        Stock mínimo <span style="color:#dc3545;">*</span>
                    </label>
                    <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 10) }}"
                           step="0.001" min="0"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;">
                    <p style="color:#aaa; font-size:0.78rem; margin-top:4px;">
                        Se mostrará alerta cuando el stock baje de este valor.
                    </p>
                    @error('stock_minimo')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="background:#8B0000; color:white; padding:10px 24px;
                               border:none; border-radius:8px; font-weight:600;
                               font-size:0.9rem; cursor:pointer;">
                    Guardar Producto
                </button>
                <a href="{{ route('productos.index') }}"
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