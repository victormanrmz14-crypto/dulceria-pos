@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')

<div style="max-width:680px;">
    <div style="margin-bottom:28px;">
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            Editar Producto
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Modifica los datos del producto.
        </p>
    </div>

    {{-- Alerta stock bajo --}}
    @if($producto->stockBajo() && $producto->activo)
    <div style="background:#fff3cd; color:#856404; padding:14px 18px; border-radius:10px;
                border-left:4px solid #f0a500; margin-bottom:20px; font-size:0.9rem;">
        ⚠️ Este producto tiene <strong>stock bajo</strong>.
        Stock actual: <strong>{{ number_format($producto->stock, 0) }}</strong> /
        Mínimo: <strong>{{ number_format($producto->stock_minimo, 0) }}</strong>
        @if($producto->proveedor)
            — Proveedor asignado: <strong>{{ $producto->proveedor->nombre }}</strong>
        @else
            — <span style="color:#dc3545;">Sin proveedor asignado</span>
        @endif
    </div>
    @endif

    <div style="background:#fff; border-radius:12px; padding:32px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <form method="POST" action="{{ route('productos.update', $producto) }}">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Nombre del producto <span style="color:#dc3545;">*</span>
                </label>
                <input type="text" name="nombre"
                       value="{{ old('nombre', $producto->nombre) }}"
                       style="width:100%; padding:10px 14px; border:1px solid #ddd;
                              border-radius:8px; font-size:0.95rem; outline:none;">
                @error('nombre')
                    <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Proveedor --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;
                              font-size:0.9rem; color:#555;">
                    Proveedor
                </label>
                <select name="proveedor_id"
                        style="width:100%; padding:10px 14px; border:1px solid #ddd;
                               border-radius:8px; font-size:0.95rem; outline:none; background:#fff;">
                    <option value="">Sin proveedor</option>
                    @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}"
                            {{ old('proveedor_id', $producto->proveedor_id) == $proveedor->id ? 'selected' : '' }}>
                            {{ $proveedor->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('proveedor_id')
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
                                {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
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
                                {{ old('marca_id', $producto->marca_id) == $marca->id ? 'selected' : '' }}>
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
                    <input type="number" name="precio"
                           value="{{ old('precio', $producto->precio) }}"
                           step="0.01" min="0"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;">
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
                        <option value="pieza"  {{ old('unidad_medida', $producto->unidad_medida) == 'pieza'   ? 'selected' : '' }}>Pieza</option>
                        <option value="kg"     {{ old('unidad_medida', $producto->unidad_medida) == 'kg'      ? 'selected' : '' }}>Kilogramo (kg)</option>
                        <option value="gramo"  {{ old('unidad_medida', $producto->unidad_medida) == 'gramo'   ? 'selected' : '' }}>Gramo</option>
                        <option value="litro"  {{ old('unidad_medida', $producto->unidad_medida) == 'litro'   ? 'selected' : '' }}>Litro</option>
                        <option value="caja"   {{ old('unidad_medida', $producto->unidad_medida) == 'caja'    ? 'selected' : '' }}>Caja</option>
                        <option value="bolsa"  {{ old('unidad_medida', $producto->unidad_medida) == 'bolsa'   ? 'selected' : '' }}>Bolsa</option>
                        <option value="paquete"{{ old('unidad_medida', $producto->unidad_medida) == 'paquete' ? 'selected' : '' }}>Paquete</option>
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
                        Stock <span style="color:#dc3545;">*</span>
                    </label>
                    <input type="number" name="stock"
                           value="{{ old('stock', $producto->stock) }}"
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
                    <input type="number" name="stock_minimo"
                           value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                           step="0.001" min="0"
                           style="width:100%; padding:10px 14px; border:1px solid #ddd;
                                  border-radius:8px; font-size:0.95rem; outline:none;">
                    @error('stock_minimo')
                        <p style="color:#dc3545; font-size:0.82rem; margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Botones --}}
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <button type="submit"
                        style="background:#8B0000; color:white; padding:10px 24px;
                               border:none; border-radius:8px; font-weight:600;
                               font-size:0.9rem; cursor:pointer;">
                    Actualizar Producto
                </button>
                <a href="{{ route('productos.index') }}"
                   style="background:#f0f0f0; color:#555; padding:10px 24px;
                          border-radius:8px; text-decoration:none; font-weight:600;
                          font-size:0.9rem;">
                    Cancelar
                </a>
            </div>
        </form>

        {{-- Botón notificar proveedor FUERA del form --}}
        @if($producto->proveedor && $producto->stockBajo() && $producto->activo)
        <div style="margin-top:16px; padding-top:16px; border-top:1px solid #f0f0f0;">
            <p style="color:#856404; font-size:0.85rem; margin-bottom:10px;">
                ⚠️ Stock bajo detectado. ¿Deseas notificar al proveedor?
            </p>
            <form method="POST"
                  action="{{ route('productos.notificar-proveedor', $producto) }}">
                @csrf
                <button type="submit"
                        style="background:#f0a500; color:white; padding:10px 24px;
                               border:none; border-radius:8px; font-weight:600;
                               font-size:0.9rem; cursor:pointer;">
                    📧 Notificar a {{ $producto->proveedor->nombre }}
                </button>
            </form>
        </div>
        @endif

    </div>
</div>

@endsection