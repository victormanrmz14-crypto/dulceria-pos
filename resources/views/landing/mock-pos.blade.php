{{-- Maqueta del Punto de Venta — replica visual de Ventas/Pos.vue con datos de ejemplo --}}
<div style="display:flex;background:#f4f4f5;font-family:'DM Sans',system-ui,sans-serif;min-height:520px">

  {{-- Sidebar --}}
  <div style="width:190px;flex-shrink:0;background:#8B0000;display:flex;flex-direction:column">
    <div style="background:#A52A2A;padding:20px 0 16px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.08)">
      <div style="color:#fff;font-family:'Playfair Display',serif;font-size:16px;font-weight:700">🍬 Dulcería POS</div>
      <div style="color:#ffcccc;font-size:11.5px;margin-top:2px">Laura M.</div>
    </div>
    <div style="flex:1;padding:8px 0">
      <div style="display:flex;align-items:center;gap:9px;padding:11px 0 11px 22px;color:#fff;font-size:13px;font-weight:500">🏠 Inicio</div>
      <div style="display:flex;align-items:center;gap:9px;padding:11px 0 11px 26px;color:#fff;font-size:13px;font-weight:500;background:rgba(255,255,255,0.12);position:relative">
        <span style="position:absolute;left:0;top:22%;width:3px;height:56%;background:#ff7043;border-radius:0 3px 3px 0"></span>🛒 Ventas
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 14px 11px 22px;color:#fff;font-size:13px;font-weight:500"><span>🏦 Caja</span><span style="font-size:10px">▼</span></div>
      <div style="background:rgba(0,0,0,0.15)">
        <div style="padding:8px 0 8px 38px;color:#ffcccc;font-size:12px;font-weight:500">💰 Caja Actual</div>
      </div>
    </div>
    <div style="background:#580000;color:#fff;text-align:center;padding:13px;font-size:12.5px;font-weight:600">🚪 Cerrar Sesión</div>
  </div>

  {{-- Contenido principal --}}
  <div style="flex:1;min-width:0;padding:20px">
    <div style="margin-bottom:14px">
      <div style="font-family:'Playfair Display',serif;color:#8B0000;font-size:22px;font-weight:700">🛒 Punto de Venta</div>
      <div style="color:#6c757d;font-size:12.5px">martes, 4 de agosto de 2026</div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;align-items:start">
      {{-- Lista de productos --}}
      <div>
        <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:8px 12px;margin-bottom:12px;display:flex;align-items:center;gap:8px">
          <span style="font-size:14px">🔍</span>
          <span style="color:#adb5bd;font-size:13px">Buscar producto por nombre...</span>
        </div>
        <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden">
          @foreach ([
            ['Paleta Payaso','124 pieza','$12.00'],
            ['Gomitas surtidas','38 kg','$20.00'],
            ['Chicles Bubbaloo','210 pieza','$8.00'],
            ['Mazapán De la Rosa','96 pieza','$9.00'],
            ['Tamarindo enchilado','54 pieza','$15.00'],
            ['Refresco 600 ml','72 pieza','$22.00'],
          ] as [$nombre, $stock, $precio])
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid #f2f2f2">
            <div style="display:flex;align-items:center;gap:11px">
              <div style="width:38px;height:38px;border-radius:8px;background:#f1f1f2;display:flex;align-items:center;justify-content:center;font-size:19px">🍬</div>
              <div>
                <div style="font-size:13px;font-weight:600;color:#2b2b2b">{{ $nombre }}</div>
                <div style="font-size:11.5px;color:#6c757d">Stock: {{ $stock }}</div>
              </div>
            </div>
            <div style="text-align:right">
              <div style="font-size:13.5px;font-weight:700;color:#8B0000">{{ $precio }}</div>
              <div style="font-size:11px;font-weight:600;color:#28a745">+ Agregar</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Carrito --}}
      <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden">
        <div style="padding:11px 14px;border-bottom:1px solid #f0f0f0;font-family:'Playfair Display',serif;color:#8B0000;font-size:15px;font-weight:700">🛒 Carrito</div>
        <div style="padding:12px 14px">
          @foreach ([
            ['Paleta Payaso','6','$72.00'],
            ['Gomitas surtidas','2','$40.00'],
            ['Chicles Bubbaloo','1','$8.00'],
          ] as [$nombre, $qty, $subtotal])
          <div style="padding:8px 0;border-bottom:1px solid #f2f2f2">
            <div style="display:flex;justify-content:space-between;gap:8px">
              <span style="font-size:12.5px;font-weight:600;color:#2b2b2b">{{ $nombre }}</span>
              <span style="color:#dc3545;font-size:12px">✕</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;margin-top:7px">
              <div style="width:56px;border:1px solid #dee2e6;border-radius:7px;padding:4px;text-align:center;font-size:12.5px;font-weight:600;color:#2b2b2b">{{ $qty }}</div>
              <span style="margin-left:auto;font-size:12.5px;color:#6c757d">{{ $subtotal }}</span>
            </div>
          </div>
          @endforeach

          <div style="padding-top:12px;margin-top:8px;border-top:1px solid #e9ecef">
            <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px"><span style="color:#6c757d">Subtotal</span><span style="font-weight:600">$142.00</span></div>
            <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:9px"><span style="color:#6c757d">IVA (16%)</span><span style="font-weight:600">$22.72</span></div>
            <div style="display:flex;justify-content:space-between;border-top:1px solid #e9ecef;padding-top:9px;margin-bottom:12px">
              <span style="font-weight:700;font-size:13.5px">Total</span>
              <span style="font-weight:700;font-size:18px;color:#8B0000">$164.72</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">
              <div style="border:2px solid #8B0000;background:#fff5f5;color:#8B0000;border-radius:8px;padding:8px;text-align:center;font-size:12.5px;font-weight:600">💵 Efectivo</div>
              <div style="border:2px solid #ddd;background:#fff;color:#777;border-radius:8px;padding:8px;text-align:center;font-size:12.5px;font-weight:600">💳 Tarjeta</div>
            </div>
            <div style="margin-bottom:12px">
              <div style="font-size:11.5px;font-weight:600;color:#6c757d;margin-bottom:4px">Monto recibido</div>
              <div style="border:1px solid #dee2e6;border-radius:8px;padding:8px 10px;text-align:right;font-size:13px;color:#2b2b2b">200.00</div>
              <div style="text-align:right;font-size:12px;font-weight:700;color:#28a745;margin-top:5px">Cambio: $35.28</div>
            </div>
            <div style="background:#8B0000;color:#fff;border-radius:10px;padding:11px;text-align:center;font-weight:700;font-size:13.5px">Cobrar $164.72</div>
            <div style="background:#f1f1f2;color:#6c757d;border-radius:10px;padding:8px;text-align:center;font-weight:600;font-size:12px;margin-top:8px">Limpiar carrito</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
