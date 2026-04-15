@extends('layouts.app')

@section('title', 'Inicio')

@section('content')

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
<style>
    .metric-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        display: block;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.13);
        text-decoration: none;
    }
    .corte-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(139,0,0,0.35);
    }
    .accion-vender:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(139,0,0,0.35);
        text-decoration: none;
    }
    .accion-corte-cajero:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(139,0,0,0.18);
    }
</style>

<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr));
            gap:20px; margin-bottom:28px;">

    <a href="{{ route('ventas.historial') }}" class="metric-card"
       style="border-left:4px solid #8B0000;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Ventas hoy</p>
        <p style="font-size:2rem; font-weight:700; color:#8B0000; margin:8px 0 0;">
            {{ $ventasHoy }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">transacciones</p>
    </a>

    <a href="{{ route('ventas.historial') }}" class="metric-card"
       style="border-left:4px solid #28a745;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Total del día</p>
        <p style="font-size:2rem; font-weight:700; color:#28a745; margin:8px 0 0;">
            ${{ number_format($totalHoy, 2) }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">ingresos</p>
    </a>

    <a href="{{ route('productos.index') }}" class="metric-card"
       style="border-left:4px solid #f0a500;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Productos activos</p>
        <p style="font-size:2rem; font-weight:700; color:#f0a500; margin:8px 0 0;">
            {{ $productosActivos }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">en catálogo</p>
    </a>

    <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="metric-card"
       style="border-left:4px solid {{ $stockBajo > 0 ? '#dc3545' : '#28a745' }};">
        <p style="color:#999; font-size:0.82rem; margin:0;">Stock bajo</p>
        <p style="font-size:2rem; font-weight:700;
                  color:{{ $stockBajo > 0 ? '#dc3545' : '#28a745' }}; margin:8px 0 0;">
            {{ $stockBajo }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">
            {{ $stockBajo > 0 ? 'requieren reabasto' : 'todo en orden' }}
        </p>
        @if($stockBajo > 0)
        <span style="display:inline-block; margin-top:10px; background:#dc3545;
                     color:#fff; font-size:0.72rem; font-weight:700;
                     padding:3px 10px; border-radius:20px;">
            Ver productos →
        </span>
        @endif
    </a>

    <a href="{{ route('caja.index') }}" class="corte-card"
       style="background:#8B0000; border-radius:12px; padding:24px 20px;
              box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none;
              cursor:pointer; transition:all 0.2s ease; text-align:center;
              display:flex; flex-direction:column; align-items:center;
              justify-content:center; gap:8px; text-decoration:none;">
        <span style="font-size:2rem; line-height:1;">🏦</span>
        <span style="font-size:1rem; font-weight:700; color:#fff;">Ir a Caja</span>
        @if($ultimoCorte)
        <span style="font-size:0.72rem; color:rgba(255,255,255,0.65); margin-top:2px;">
            Último corte: {{ $ultimoCorte->fecha_corte->isoFormat('D MMM [a las] HH:mm') }}
        </span>
        @else
        <span style="font-size:0.72rem; color:rgba(255,255,255,0.55); margin-top:2px;">
            Sin cortes aún
        </span>
        @endif
    </a>
</div>

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

{{-- Tarjetas cajero (4 métricas + 2 acción) --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr));
            gap:20px; margin-bottom:16px;">

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

    <a href="{{ route('ventas.index') }}" class="accion-vender"
       style="background:#8B0000; border-radius:12px; padding:24px 20px;
              box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none;
              text-decoration:none; cursor:pointer; transition:all 0.2s ease;
              display:flex; flex-direction:column; align-items:center;
              justify-content:center; gap:8px; text-align:center;">
        <span style="font-size:2rem; line-height:1;">🛒</span>
        <span style="font-size:1rem; font-weight:700; color:#fff;">Ir a vender</span>
    </a>

    <a href="{{ route('caja.index') }}" class="accion-corte-cajero"
       style="background:#fff; border-radius:12px; padding:24px 20px;
              box-shadow:0 2px 8px rgba(0,0,0,0.06); border:2px solid #8B0000;
              cursor:pointer; transition:all 0.2s ease; text-decoration:none;
              display:flex; flex-direction:column; align-items:center;
              justify-content:center; gap:8px;">
        <span style="font-size:2rem; line-height:1;">🏦</span>
        <span style="font-size:1rem; font-weight:700; color:#8B0000;">Ir a Caja</span>
    </a>
</div>

{{-- Último corte --}}
@if($ultimoCorte)
<p style="color:#aaa; font-size:0.78rem; margin-bottom:28px;">
    Último corte: {{ $ultimoCorte->fecha_corte->isoFormat('D MMM YYYY [a las] HH:mm') }}
    —
    <a href="{{ route('cortes.show', $ultimoCorte) }}"
       style="color:#8B0000; font-weight:600; text-decoration:none;">Ver detalle</a>
</p>
@else
<p style="color:#aaa; font-size:0.78rem; margin-bottom:28px;">Sin cortes registrados aún.</p>
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

@endsection