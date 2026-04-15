<div style="display:grid; grid-template-columns:1fr 400px; gap:20px; height:calc(100vh - 160px);">

    {{-- COLUMNA IZQUIERDA: Buscador + Productos --}}
    <div style="display:flex; flex-direction:column; gap:12px; overflow:hidden;">

        {{-- Buscador --}}
        <div style="background:#fff; border-radius:12px; padding:16px 20px;
                    box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <div style="position:relative;">
                <span style="position:absolute; left:14px; top:50%;
                             transform:translateY(-50%); color:#aaa; font-size:1.1rem;">🔍</span>
                <input type="text"
                       wire:model.live.debounce.300ms="buscar"
                       placeholder="Buscar producto por nombre..."
                       style="width:100%; padding:11px 16px 11px 42px; border:2px solid #eee;
                              border-radius:8px; font-size:0.95rem; outline:none;
                              transition:border 0.2s;"
                       onfocus="this.style.borderColor='#8B0000'"
                       onblur="this.style.borderColor='#eee'">
            </div>
        </div>

        {{-- Lista de productos --}}
        <div style="flex:1; overflow-y:auto;">
            <div style="background:#fff; border-radius:12px;
                        box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
                @forelse($this->productos as $producto)
                <div wire:key="prod-{{ $producto->id }}"
                     wire:click="agregarProducto({{ $producto->id }})"
                     style="display:flex; justify-content:space-between; align-items:center;
                            padding:14px 20px; border-bottom:1px solid #f5f5f5; cursor:pointer;
                            transition:background 0.15s;"
                     onmouseover="this.style.background='#fff5f5'"
                     onmouseout="this.style.background='transparent'">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div style="width:42px; height:42px; background:#f5f5f5; border-radius:8px;
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:1.4rem; flex-shrink:0;">
                            🍬
                        </div>
                        <div>
                            <p style="font-weight:600; color:#333; font-size:0.92rem; margin:0;">
                                {{ $producto->nombre }}
                            </p>
                            <p style="color:#aaa; font-size:0.78rem; margin:2px 0 0;">
                                Stock: {{ number_format($producto->stock, 0) }} {{ $producto->unidad_medida }}
                            </p>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-weight:700; color:#8B0000; font-size:1rem; margin:0;">
                            ${{ number_format($producto->precio, 2) }}
                        </p>
                        <p style="color:#28a745; font-size:0.75rem; margin:2px 0 0; font-weight:600;">
                            + Agregar
                        </p>
                    </div>
                </div>
                @empty
                <div style="text-align:center; padding:40px; color:#bbb;">
                    <p style="font-size:2rem; margin:0;">🍬</p>
                    <p style="font-size:0.9rem; margin:8px 0 0;">No se encontraron productos.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
    {{-- ← Fin columna izquierda --}}

    {{-- COLUMNA DERECHA: Carrito --}}
    <div style="background:#fff; border-radius:12px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);
                display:flex; flex-direction:column; gap:12px; overflow:hidden;">

        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.2rem; margin:0; padding-bottom:12px;
                   border-bottom:1px solid #f0f0f0; flex-shrink:0;">
            🛒 Carrito
        </h3>

        {{-- Items --}}
        <div style="flex:1; overflow-y:auto;">
            @forelse($carrito as $item)
            <div wire:key="cart-{{ $item['id'] }}"
                 style="padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <div style="display:flex; justify-content:space-between; align-items:start; gap:8px;">
                    <p style="font-weight:600; color:#333; font-size:0.88rem;
                              margin:0; flex:1; line-height:1.3;">
                        {{ $item['nombre'] }}
                    </p>
                    <button wire:click="quitarProducto({{ $item['id'] }})"
                            style="background:none; border:none; color:#dc3545;
                                   cursor:pointer; font-size:0.9rem; padding:0; flex-shrink:0;">
                        ✕
                    </button>
                </div>
                <div style="display:flex; align-items:center; margin-top:8px;">
    <input type="number"
           wire:change="cambiarCantidad({{ $item['id'] }}, $event.target.value)"
           value="{{ $item['cantidad'] }}"
           min="1"
           max="{{ $item['stock'] }}"
           style="width:64px; height:32px; border:1px solid #ddd; border-radius:6px;
                  text-align:center; font-weight:600; font-size:0.9rem; outline:none;
                  padding:0 8px;">
</div>
            </div>
            @empty
            <div style="text-align:center; padding:40px 0; color:#ccc;">
                <p style="font-size:2.5rem; margin:0;">🛒</p>
                <p style="font-size:0.85rem; margin:8px 0 0;">
                    Agrega productos haciendo clic
                </p>
            </div>
            @endforelse
        </div>

        {{-- Totales y cobro --}}
        @if(!empty($carrito))
        <div style="border-top:2px solid #f0f0f0; padding-top:14px; flex-shrink:0;">

            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span style="color:#777; font-size:0.85rem;">Subtotal</span>
                <span style="font-weight:600; font-size:0.85rem;">
                    ${{ number_format($this->subtotal, 2) }}
                </span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:#777; font-size:0.85rem;">IVA (16%)</span>
                <span style="font-weight:600; font-size:0.85rem;">
                    ${{ number_format($this->iva, 2) }}
                </span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:14px;
                        padding-top:10px; border-top:1px solid #f0f0f0;">
                <span style="font-weight:700; font-size:1rem;">Total</span>
                <span style="font-weight:700; font-size:1.2rem; color:#8B0000;">
                    ${{ number_format($this->total, 2) }}
                </span>
            </div>

            {{-- Método de pago --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                <div wire:click="$set('metodoPago', 'efectivo')"
                     style="display:flex; align-items:center; justify-content:center; gap:8px;
                            padding:10px; border-radius:8px; cursor:pointer; font-size:0.88rem;
                            font-weight:600; border:2px solid {{ $metodoPago === 'efectivo' ? '#8B0000' : '#ddd' }};
                            background:{{ $metodoPago === 'efectivo' ? '#fff5f5' : '#fff' }};
                            color:{{ $metodoPago === 'efectivo' ? '#8B0000' : '#777' }};">
                    💵 Efectivo
                </div>
                <div wire:click="$set('metodoPago', 'tarjeta')"
                     style="display:flex; align-items:center; justify-content:center; gap:8px;
                            padding:10px; border-radius:8px; cursor:pointer; font-size:0.88rem;
                            font-weight:600; border:2px solid {{ $metodoPago === 'tarjeta' ? '#8B0000' : '#ddd' }};
                            background:{{ $metodoPago === 'tarjeta' ? '#fff5f5' : '#fff' }};
                            color:{{ $metodoPago === 'tarjeta' ? '#8B0000' : '#777' }};">
                    💳 Tarjeta
                </div>
            </div>

            {{-- Monto recibido (solo efectivo) --}}
            @if($metodoPago === 'efectivo')
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:0.82rem; font-weight:600;
                              color:#555; margin-bottom:6px;">
                    Monto recibido
                </label>
                <input type="number"
                       wire:model.live="montoRecibido"
                       step="0.01"
                       min="{{ $this->total }}"
                       placeholder="0.00"
                       style="width:100%; padding:9px 12px; border:1px solid #ddd;
                              border-radius:8px; font-size:0.95rem; outline:none;
                              text-align:right;">
                @if($this->cambio > 0)
                <p style="margin:6px 0 0; text-align:right; font-weight:700;
                          color:#28a745; font-size:0.95rem;">
                    Cambio: ${{ number_format($this->cambio, 2) }}
                </p>
                @endif
            </div>
            @endif

            <button wire:click="confirmarVenta"
                    style="width:100%; padding:14px; background:#8B0000; color:white;
                           border:none; border-radius:10px; font-weight:700;
                           font-size:1rem; cursor:pointer;"
                    onmouseover="this.style.background='#6d0000'"
                    onmouseout="this.style.background='#8B0000'">
                Cobrar ${{ number_format($this->total, 2) }}
            </button>

            <button wire:click="limpiarCarrito"
                    style="width:100%; padding:9px; background:#f5f5f5; color:#777;
                           border:none; border-radius:8px; font-weight:600;
                           font-size:0.85rem; cursor:pointer; margin-top:8px;">
                Limpiar carrito
            </button>
        </div>
        @endif

    </div>
    {{-- ← Fin columna derecha --}}

    {{-- Modal de confirmación --}}
    @if($mostrarConfirmacion)
    <div style="position:fixed; inset:0; background:rgba(0,0,0,0.5);
                display:flex; align-items:center; justify-content:center; z-index:999;">
        <div style="background:#fff; border-radius:16px; padding:32px;
                    width:380px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                       font-size:1.4rem; margin:0 0 8px;">
                Confirmar venta
            </h3>
            <p style="color:#777; font-size:0.9rem; margin:0 0 6px;">
                Total:
                <strong style="color:#8B0000; font-size:1.1rem;">
                    ${{ number_format($this->total, 2) }}
                </strong>
            </p>
            <p style="color:#777; font-size:0.9rem; margin:0 0 4px;">
                Método: {{ $metodoPago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}
            </p>
            @if($metodoPago === 'efectivo' && $montoRecibido > 0)
            <p style="color:#777; font-size:0.9rem; margin:0 0 4px;">
                Recibido: <strong>${{ number_format($montoRecibido, 2) }}</strong>
            </p>
            @endif
            @if($this->cambio > 0)
            <p style="color:#28a745; font-size:0.9rem; font-weight:700; margin:0 0 24px;">
                Cambio: ${{ number_format($this->cambio, 2) }}
            </p>
            @else
            <p style="margin:0 0 24px;"></p>
            @endif
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button wire:click="cancelarConfirmacion"
                        style="padding:12px; background:#f0f0f0; color:#555; border:none;
                               border-radius:8px; font-weight:600; cursor:pointer;">
                    Cancelar
                </button>
                <button wire:click="procesarVenta"
                        style="padding:12px; background:#8B0000; color:white; border:none;
                               border-radius:8px; font-weight:700; cursor:pointer;">
                    ✅ Confirmar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>