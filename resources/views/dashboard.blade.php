@extends('layouts.app')

@section('title', 'Inicio')

@section('content')

<div x-data="{ modalCorte: false, notas: '' }">

@if(auth()->user()->rol === 'admin')

{{-- ═══════════════════════════════ VISTA ADMIN ═══════════════════════════════ --}}

<div style="margin-bottom:28px;">
    <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
        Bienvenido, {{ Auth::user()->nombre }} 👋
    </h2>
    <p style="color:#999; font-size:0.9rem; margin-top:4px;">
        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    </p>
</div>

{{-- Tarjetas admin --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr));
            gap:20px; margin-bottom:28px;">

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #8B0000;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Ventas hoy</p>
        <p style="font-size:2rem; font-weight:700; color:#8B0000; margin:8px 0 0;">
            {{ $ventasHoy }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">transacciones</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #28a745;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Total del día</p>
        <p style="font-size:2rem; font-weight:700; color:#28a745; margin:8px 0 0;">
            ${{ number_format($totalHoy, 2) }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">ingresos</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #f0a500;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Productos activos</p>
        <p style="font-size:2rem; font-weight:700; color:#f0a500; margin:8px 0 0;">
            {{ $productosActivos }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">en catálogo</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);
                border-left:4px solid {{ $stockBajo > 0 ? '#dc3545' : '#28a745' }};">
        <p style="color:#999; font-size:0.82rem; margin:0;">Stock bajo</p>
        <p style="font-size:2rem; font-weight:700;
                  color:{{ $stockBajo > 0 ? '#dc3545' : '#28a745' }}; margin:8px 0 0;">
            {{ $stockBajo }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">
            {{ $stockBajo > 0 ? 'requieren reabasto' : 'todo en orden' }}
        </p>
    </div>
</div>

{{-- Alerta stock bajo --}}
@if($stockBajo > 0)
<div style="background:#fff3cd; color:#856404; padding:14px 18px; border-radius:10px;
            border-left:4px solid #f0a500; margin-bottom:28px; font-size:0.9rem;">
    ⚠️ <strong>{{ $stockBajo }} producto(s)</strong> tienen stock por debajo del mínimo.
    <a href="{{ route('productos.index') }}"
       style="color:#8B0000; font-weight:600; margin-left:8px;">Ver productos →</a>
</div>
@endif

{{-- Gráfica + más vendidos --}}
<div style="display:grid; grid-template-columns:1fr 340px; gap:20px; margin-bottom:28px;">

    <div style="background:#fff; border-radius:12px; padding:24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.1rem; margin:0 0 20px;">
            📈 Ventas últimos 7 días
        </h3>
        <canvas id="graficaVentas" height="120"></canvas>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.1rem; margin:0 0 16px;">
            🏆 Más vendidos
        </h3>
        @forelse($masVendidos as $item)
        <div style="display:flex; justify-content:space-between; align-items:center;
                    padding:10px 0; border-bottom:1px solid #f5f5f5;">
            <div>
                <p style="font-weight:600; color:#333; font-size:0.88rem; margin:0;">
                    {{ $item->producto->nombre ?? '—' }}
                </p>
                <p style="color:#aaa; font-size:0.75rem; margin:2px 0 0;">
                    {{ number_format($item->total_vendido, 0) }} unidades
                </p>
            </div>
            <p style="font-weight:700; color:#8B0000; font-size:0.9rem; margin:0;">
                ${{ number_format($item->total_importe, 2) }}
            </p>
        </div>
        @empty
        <p style="color:#bbb; font-size:0.9rem; text-align:center; padding:20px 0;">
            Sin ventas registradas.
        </p>
        @endforelse
    </div>
</div>

{{-- Últimas ventas --}}
<div style="background:#fff; border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <div style="padding:16px 20px; border-bottom:1px solid #f0f0f0;
                display:flex; justify-content:space-between; align-items:center;">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.1rem; margin:0;">Últimas ventas del día</h3>
        <a href="{{ route('ventas.historial') }}"
           style="color:#8B0000; font-size:0.85rem; font-weight:600; text-decoration:none;">
            Ver todas →
        </a>
    </div>
    @forelse($ultimasVentas as $venta)
    <div style="display:flex; justify-content:space-between; align-items:center;
                padding:14px 20px; border-bottom:1px solid #f5f5f5;">
        <div>
            <p style="font-weight:600; color:#333; margin:0; font-size:0.9rem;">
                {{ $venta->folio }}
            </p>
            <p style="color:#aaa; font-size:0.78rem; margin:3px 0 0;">
                {{ $venta->created_at->format('H:i') }} — {{ $venta->usuario->nombre ?? '—' }}
            </p>
        </div>
        <div style="text-align:right;">
            <p style="font-weight:700; color:#8B0000; margin:0;">
                ${{ number_format($venta->total, 2) }}
            </p>
            <p style="color:#aaa; font-size:0.78rem; margin:3px 0 0;">
                {{ $venta->metodo_pago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}
            </p>
        </div>
    </div>
    @empty
    <div style="padding:40px; text-align:center; color:#bbb;">
        <p style="font-size:1.5rem; margin:0;">📊</p>
        <p style="font-size:0.9rem; margin:8px 0 0;">No hay ventas registradas hoy.</p>
    </div>
    @endforelse
</div>

{{-- Corte de caja admin --}}
<div style="display:flex; gap:16px; align-items:center; margin-top:20px;">
    <button @click="modalCorte = true"
            style="background:#fff; color:#8B0000; padding:12px 28px; border-radius:10px;
                   border:2px solid #8B0000; font-weight:700; font-size:0.95rem; cursor:pointer;">
        📋 Hacer corte
    </button>
    @if($ultimoCorte)
    <p style="color:#aaa; font-size:0.8rem; margin:0;">
        Último corte propio: {{ $ultimoCorte->fecha_corte->isoFormat('D MMM [a las] HH:mm') }}
        —
        <a href="{{ route('cortes.show', $ultimoCorte) }}"
           style="color:#8B0000; font-weight:600; text-decoration:none;">Ver</a>
        &nbsp;·&nbsp;
        <a href="{{ route('cortes.index') }}"
           style="color:#8B0000; font-weight:600; text-decoration:none;">Ver todos →</a>
    </p>
    @else
    <a href="{{ route('cortes.index') }}"
       style="color:#8B0000; font-weight:600; font-size:0.8rem; text-decoration:none;">
        Ver historial de cortes →
    </a>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficaVentas').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($ultimos7Dias->pluck('fecha')) !!},
            datasets: [{
                label: 'Total vendido',
                data: {!! json_encode($ultimos7Dias->pluck('total')) !!},
                borderColor: '#8B0000',
                backgroundColor: 'rgba(139,0,0,0.08)',
                borderWidth: 2,
                pointBackgroundColor: '#8B0000',
                pointRadius: 4,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => '$' + ctx.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2})
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: val => '$' + val.toLocaleString('es-MX') }
                }
            }
        }
    });
</script>

@else

{{-- ═══════════════════════════════ VISTA CAJERO ═══════════════════════════════ --}}

<div style="margin-bottom:28px;">
    <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
        Hola, {{ Auth::user()->nombre }} 👋
    </h2>
    <p style="color:#999; font-size:0.9rem; margin-top:4px;">
        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    </p>
</div>

{{-- Tarjetas cajero --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr));
            gap:20px; margin-bottom:28px;">

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #8B0000;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Mis ventas hoy</p>
        <p style="font-size:2rem; font-weight:700; color:#8B0000; margin:8px 0 0;">
            {{ $misVentasHoy }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">transacciones</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #28a745;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Mi total hoy</p>
        <p style="font-size:2rem; font-weight:700; color:#28a745; margin:8px 0 0;">
            ${{ number_format($miTotalHoy, 2) }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">vendido</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #17a2b8;">
        <p style="color:#999; font-size:0.82rem; margin:0;">💵 Efectivo</p>
        <p style="font-size:2rem; font-weight:700; color:#17a2b8; margin:8px 0 0;">
            ${{ number_format($miEfectivo, 2) }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">cobrado</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #6f42c1;">
        <p style="color:#999; font-size:0.82rem; margin:0;">💳 Tarjeta</p>
        <p style="font-size:2rem; font-weight:700; color:#6f42c1; margin:8px 0 0;">
            ${{ number_format($miTarjeta, 2) }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">cobrado</p>
    </div>
</div>

{{-- Botones de acción --}}
<div style="display:flex; gap:16px; align-items:center; margin-bottom:16px;">
    <a href="{{ route('ventas.index') }}"
       style="background:#8B0000; color:white; padding:14px 32px; border-radius:10px;
              text-decoration:none; font-weight:700; font-size:1rem;">
        🛒 Ir a vender
    </a>
    <button @click="modalCorte = true"
            style="background:#fff; color:#8B0000; padding:14px 32px; border-radius:10px;
                   border:2px solid #8B0000; font-weight:700; font-size:1rem; cursor:pointer;">
        📋 Hacer corte
    </button>
</div>
@if($ultimoCorte)
<p style="color:#aaa; font-size:0.8rem; margin-bottom:28px;">
    Último corte: {{ $ultimoCorte->fecha_corte->isoFormat('D MMM YYYY [a las] HH:mm') }}
    —
    <a href="{{ route('cortes.show', $ultimoCorte) }}"
       style="color:#8B0000; font-weight:600; text-decoration:none;">Ver detalle</a>
</p>
@else
<p style="color:#aaa; font-size:0.8rem; margin-bottom:28px;">Sin cortes registrados aún.</p>
@endif

{{-- Mis últimas ventas --}}
<div style="background:#fff; border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <div style="padding:16px 20px; border-bottom:1px solid #f0f0f0;">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.1rem; margin:0;">Mis últimas ventas</h3>
    </div>
    @forelse($misUltimasVentas as $venta)
    <div style="display:flex; justify-content:space-between; align-items:center;
                padding:14px 20px; border-bottom:1px solid #f5f5f5;">
        <div>
            <p style="font-weight:600; color:#333; margin:0; font-size:0.9rem;">
                {{ $venta->folio }}
            </p>
            <p style="color:#aaa; font-size:0.78rem; margin:3px 0 0;">
                {{ $venta->created_at->format('H:i') }}
            </p>
        </div>
        <div style="text-align:right;">
            <p style="font-weight:700; color:#8B0000; margin:0;">
                ${{ number_format($venta->total, 2) }}
            </p>
            <p style="color:#aaa; font-size:0.78rem; margin:3px 0 0;">
                {{ $venta->metodo_pago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}
            </p>
        </div>
    </div>
    @empty
    <div style="padding:40px; text-align:center; color:#bbb;">
        <p style="font-size:1.5rem; margin:0;">🛒</p>
        <p style="font-size:0.9rem; margin:8px 0 0;">No has registrado ventas hoy.</p>
    </div>
    @endforelse
</div>

@endif

{{-- ═══════════════ MODAL CORTE DE CAJA ═══════════════ --}}
<div x-show="modalCorte"
     x-transition.opacity
     style="position:fixed; inset:0; background:rgba(0,0,0,0.5);
            display:flex; align-items:center; justify-content:center; z-index:1000;"
     @keydown.escape.window="modalCorte = false">

    <div style="background:#fff; border-radius:16px; padding:32px; width:440px;
                box-shadow:0 24px 64px rgba(0,0,0,0.25);"
         @click.stop>

        {{-- Encabezado --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                       font-size:1.4rem; margin:0;">
                📋 Corte de Caja
            </h3>
            <button @click="modalCorte = false"
                    style="background:none; border:none; font-size:1.2rem;
                           cursor:pointer; color:#aaa; line-height:1;">✕</button>
        </div>

        {{-- Tabla de totales del turno --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:0.9rem;">
            <thead>
                <tr style="background:#f9f9f9; border-bottom:2px solid #eee;">
                    <th style="padding:10px 14px; text-align:left; color:#777;
                               font-weight:600; font-size:0.82rem;">Método</th>
                    <th style="padding:10px 14px; text-align:right; color:#777;
                               font-weight:600; font-size:0.82rem;">Total del turno</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:12px 14px;">
                        <span style="display:inline-flex; align-items:center; gap:8px;">
                            <span style="width:10px; height:10px; border-radius:50%;
                                         background:#17a2b8; display:inline-block;"></span>
                            💵 Efectivo
                        </span>
                    </td>
                    <td style="padding:12px 14px; text-align:right; font-weight:600;
                               color:#17a2b8;">
                        ${{ number_format($miEfectivo, 2) }}
                    </td>
                </tr>
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:12px 14px;">
                        <span style="display:inline-flex; align-items:center; gap:8px;">
                            <span style="width:10px; height:10px; border-radius:50%;
                                         background:#6f42c1; display:inline-block;"></span>
                            💳 Tarjeta
                        </span>
                    </td>
                    <td style="padding:12px 14px; text-align:right; font-weight:600;
                               color:#6f42c1;">
                        ${{ number_format($miTarjeta, 2) }}
                    </td>
                </tr>
                <tr style="background:#fff5f5;">
                    <td style="padding:12px 14px; font-weight:700; color:#333;">
                        Total general
                    </td>
                    <td style="padding:12px 14px; text-align:right; font-weight:700;
                               font-size:1.05rem; color:#8B0000;">
                        ${{ number_format($miTotalHoy, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Notas opcionales --}}
        <div style="margin-bottom:24px;">
            <label style="display:block; font-size:0.85rem; font-weight:600;
                          color:#555; margin-bottom:6px;">
                Notas <span style="font-weight:400; color:#aaa;">(opcional)</span>
            </label>
            <textarea x-model="notas"
                      rows="2"
                      placeholder="Ej. Turno tarde, cajero Juan..."
                      style="width:100%; padding:10px 12px; border:1px solid #ddd;
                             border-radius:8px; font-size:0.9rem; outline:none;
                             resize:none; font-family:inherit;"></textarea>
        </div>

        {{-- Botones --}}
        <form method="POST" action="{{ route('cortes.store') }}">
            @csrf
            <input type="hidden" name="notas" :value="notas">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button type="button"
                        @click="modalCorte = false; notas = ''"
                        style="padding:12px; background:#f0f0f0; color:#555; border:none;
                               border-radius:8px; font-weight:600; font-size:0.9rem;
                               cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding:12px; background:#8B0000; color:#fff; border:none;
                               border-radius:8px; font-weight:700; font-size:0.9rem;
                               cursor:pointer;">
                    ✅ Confirmar corte
                </button>
            </div>
        </form>

    </div>
</div>

</div>{{-- cierre x-data --}}

@endsection