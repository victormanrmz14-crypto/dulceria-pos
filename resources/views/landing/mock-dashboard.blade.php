{{-- Maqueta del Dashboard — replica visual de Dashboard.vue con datos de ejemplo --}}
<div style="display:flex;background:#f4f4f5;font-family:'DM Sans',system-ui,sans-serif;min-height:520px">

  {{-- Sidebar --}}
  <div style="width:190px;flex-shrink:0;background:#8B0000;display:flex;flex-direction:column">
    <div style="background:#A52A2A;padding:20px 0 16px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.08)">
      <div style="color:#fff;font-family:'Playfair Display',serif;font-size:16px;font-weight:700">🍬 Dulcería POS</div>
      <div style="color:#ffcccc;font-size:11.5px;margin-top:2px">Victor R.</div>
    </div>
    <div style="flex:1;padding:8px 0">
      <div style="display:flex;align-items:center;gap:9px;padding:11px 0 11px 22px;color:#fff;font-size:13px;font-weight:500;background:rgba(255,255,255,0.12);position:relative">
        <span style="position:absolute;left:0;top:22%;width:3px;height:56%;background:#ff7043;border-radius:0 3px 3px 0"></span>🏠 Inicio
      </div>
      <div style="display:flex;align-items:center;gap:9px;padding:11px 0 11px 22px;color:#fff;font-size:13px;font-weight:500">🛒 Ventas</div>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 14px 11px 22px;color:#fff;font-size:13px;font-weight:500"><span>🏦 Caja</span><span style="font-size:10px">▼</span></div>
      <div style="background:rgba(0,0,0,0.15)">
        <div style="padding:8px 0 8px 38px;color:#ffcccc;font-size:12px;font-weight:500">💰 Caja Actual</div>
        <div style="padding:8px 0 8px 38px;color:#ffcccc;font-size:12px;font-weight:500">📋 Cortes Históricos</div>
      </div>
      <div style="display:flex;align-items:center;gap:9px;padding:11px 0 11px 22px;color:#fff;font-size:13px;font-weight:500">🍬 Productos</div>
      <div style="display:flex;align-items:center;gap:9px;padding:11px 0 11px 22px;color:#fff;font-size:13px;font-weight:500">👥 Usuarios</div>
      <div style="display:flex;align-items:center;gap:9px;padding:11px 0 11px 22px;color:#fff;font-size:13px;font-weight:500">📊 Reportes</div>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 14px 11px 22px;color:#fff;font-size:13px;font-weight:500"><span>📝 Catálogos</span><span style="font-size:10px">▼</span></div>
    </div>
    <div style="background:#580000;color:#fff;text-align:center;padding:13px;font-size:12.5px;font-weight:600">🚪 Cerrar Sesión</div>
  </div>

  {{-- Contenido principal --}}
  <div style="flex:1;min-width:0;padding:20px">
    <div style="margin-bottom:18px">
      <div style="font-family:'Playfair Display',serif;color:#8B0000;font-size:22px;font-weight:700">Bienvenido, Victor 👋</div>
      <div style="color:#6c757d;font-size:12.5px">martes, 4 de agosto de 2026</div>
    </div>

    {{-- Stat cards --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:18px">
      <div style="background:#fff;border-left:4px solid #8B0000;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:12px 14px">
        <div style="color:#6c757d;font-size:11.5px;margin-bottom:2px">Ventas hoy</div>
        <div style="font-size:24px;font-weight:700;color:#8B0000;line-height:1.1">48</div>
        <div style="color:#6c757d;font-size:11px;margin-top:2px">transacciones</div>
      </div>
      <div style="background:#fff;border-left:4px solid #28a745;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:12px 14px">
        <div style="color:#6c757d;font-size:11.5px;margin-bottom:2px">Total del día</div>
        <div style="font-size:24px;font-weight:700;color:#28a745;line-height:1.1">$4,330</div>
        <div style="color:#6c757d;font-size:11px;margin-top:2px">ingresos</div>
      </div>
      <div style="background:#fff;border-left:4px solid #ffc107;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:12px 14px">
        <div style="color:#6c757d;font-size:11.5px;margin-bottom:2px">Productos activos</div>
        <div style="font-size:24px;font-weight:700;color:#ffc107;line-height:1.1">312</div>
        <div style="color:#6c757d;font-size:11px;margin-top:2px">en catálogo</div>
      </div>
      <div style="background:#fff;border-left:4px solid #dc3545;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:12px 14px">
        <div style="color:#6c757d;font-size:11.5px;margin-bottom:2px">Stock bajo</div>
        <div style="font-size:24px;font-weight:700;color:#dc3545;line-height:1.1">7</div>
        <div style="color:#6c757d;font-size:11px;margin-top:2px">requieren reabasto</div>
      </div>
      <div style="background:#8B0000;color:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center">
        <div style="font-size:22px">🏦</div>
        <div style="font-weight:700;font-size:13px">Ir a Caja</div>
        <div style="font-size:10.5px;opacity:0.75">Último corte: 03/08</div>
      </div>
    </div>

    {{-- Gráfica + Más vendidos --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:12px">
      <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden">
        <div style="padding:11px 16px;border-bottom:1px solid #f0f0f0;font-family:'Playfair Display',serif;color:#8B0000;font-size:15px;font-weight:700">📈 Ventas últimos 7 días</div>
        <div style="padding:14px 16px">
          <svg viewBox="0 0 420 140" preserveAspectRatio="none" style="width:100%;height:150px;display:block">
            <path d="M0,110 L70,86 L140,95 L210,52 L280,64 L350,34 L420,46 L420,140 L0,140 Z" fill="rgba(139,0,0,0.08)"/>
            <path d="M0,110 L70,86 L140,95 L210,52 L280,64 L350,34 L420,46" fill="none" stroke="#8B0000" stroke-width="2.5" stroke-linejoin="round"/>
            <g fill="#8B0000">
              <circle cx="0"   cy="110" r="3.5"/><circle cx="70"  cy="86"  r="3.5"/>
              <circle cx="140" cy="95"  r="3.5"/><circle cx="210" cy="52"  r="3.5"/>
              <circle cx="280" cy="64"  r="3.5"/><circle cx="350" cy="34"  r="3.5"/>
              <circle cx="420" cy="46"  r="3.5"/>
            </g>
          </svg>
          <div style="display:flex;justify-content:space-between;color:#adb5bd;font-size:10.5px;margin-top:6px">
            <span>29/07</span><span>30/07</span><span>31/07</span><span>01/08</span><span>02/08</span><span>03/08</span><span>04/08</span>
          </div>
        </div>
      </div>
      <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden">
        <div style="padding:11px 16px;border-bottom:1px solid #f0f0f0;font-family:'Playfair Display',serif;color:#8B0000;font-size:15px;font-weight:700">🏆 Más vendidos</div>
        <div style="padding:4px 16px 10px">
          @foreach ([
            ['1','Paleta Payaso','184 unidades','$2,208'],
            ['2','Gomitas surtidas 1kg','96 unidades','$1,920'],
            ['3','Chicles Bubbaloo','88 unidades','$704'],
            ['4','Mazapán De la Rosa','72 unidades','$648'],
          ] as [$pos, $nombre, $unidades, $monto])
          <div style="display:flex;align-items:center;gap:9px;padding:7px 0;border-bottom:1px solid #f2f2f2">
            <span style="background:#8B0000;color:#fff;border-radius:999px;font-size:10.5px;font-weight:600;padding:2px 8px">{{ $pos }}</span>
            <div style="flex:1;min-width:0">
              <div style="font-size:12.5px;font-weight:600;color:#2b2b2b">{{ $nombre }}</div>
              <div style="font-size:11px;color:#6c757d">{{ $unidades }}</div>
            </div>
            <span style="font-size:12.5px;font-weight:700;color:#8B0000">{{ $monto }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Últimas ventas --}}
    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden">
      <div style="padding:11px 16px;border-bottom:1px solid #f0f0f0;font-family:'Playfair Display',serif;color:#8B0000;font-size:15px;font-weight:700;display:flex;justify-content:space-between;align-items:center">
        <span>Últimas ventas del día</span>
        <span style="font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600">Ver todas →</span>
      </div>
      <table style="width:100%;border-collapse:collapse">
        <tbody>
          @foreach ([
            ['V-000482','18:42 — Laura M.','$168.50','💵 Efectivo'],
            ['V-000481','18:31 — Laura M.','$92.00', '💳 Tarjeta'],
            ['V-000480','18:14 — Victor R.','$245.00','💵 Efectivo'],
          ] as [$folio, $hora, $total, $metodo])
          <tr>
            <td style="padding:9px 16px;border-bottom:1px solid #f2f2f2">
              <div style="font-size:12.5px;font-weight:700;color:#8B0000">{{ $folio }}</div>
              <div style="font-size:11px;color:#6c757d">{{ $hora }}</div>
            </td>
            <td style="padding:9px 16px;border-bottom:1px solid #f2f2f2;text-align:right">
              <div style="font-size:12.5px;font-weight:700;color:#8B0000">{{ $total }}</div>
              <div style="font-size:11px;color:#6c757d">{{ $metodo }}</div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
