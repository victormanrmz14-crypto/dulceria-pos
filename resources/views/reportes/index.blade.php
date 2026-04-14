@extends('layouts.app')

@section('title', 'Reportes')

@section('content')

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            📊 Reportes
        </h2>
        <p style="color:#999; font-size:0.85rem; margin-top:4px;">
            {{ $fechaInicio->isoFormat('D [de] MMMM') }} —
            {{ $fechaFin->isoFormat('D [de] MMMM [de] YYYY') }}
        </p>
    </div>

    {{-- Filtros de período --}}
    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
        @foreach(['hoy' => 'Hoy', 'ayer' => 'Ayer', '7dias' => 'Últimos 7 días', '30dias' => 'Últimos 30 días'] as $key => $label)
        <a href="{{ route('reportes.index', ['periodo' => $key]) }}"
           style="padding:8px 16px; border-radius:8px; font-size:0.85rem; font-weight:600;
                  text-decoration:none; border:2px solid {{ $periodo === $key ? '#8B0000' : '#e0e0e0' }};
                  background:{{ $periodo === $key ? '#8B0000' : '#fff' }};
                  color:{{ $periodo === $key ? '#fff' : '#555' }};">
            {{ $label }}
        </a>
        @endforeach

        {{-- Rango personalizado --}}
        <form method="GET" action="{{ route('reportes.index') }}"
              style="display:flex; gap:8px; align-items:center;">
            <input type="hidden" name="periodo" value="personalizado">
            <input type="date" name="desde"
                   value="{{ $periodo === 'personalizado' ? $fechaInicio->toDateString() : '' }}"
                   style="padding:7px 12px; border:2px solid #e0e0e0; border-radius:8px;
                          font-size:0.85rem; outline:none;">
            <span style="color:#999; font-size:0.85rem;">al</span>
            <input type="date" name="hasta"
                   value="{{ $periodo === 'personalizado' ? $fechaFin->toDateString() : '' }}"
                   style="padding:7px 12px; border:2px solid #e0e0e0; border-radius:8px;
                          font-size:0.85rem; outline:none;">
            <button type="submit"
                    style="padding:8px 16px; background:#8B0000; color:#fff; border:none;
                           border-radius:8px; font-size:0.85rem; font-weight:600; cursor:pointer;">
                Aplicar
            </button>
        </form>
    </div>
</div>

{{-- Tarjetas métricas --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr));
            gap:20px; margin-bottom:28px;">

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #8B0000;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Total ventas</p>
        <p style="font-size:2rem; font-weight:700; color:#8B0000; margin:8px 0 0;">
            {{ $totalVentas }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">transacciones</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #28a745;">
        <p style="color:#999; font-size:0.82rem; margin:0;">Ingresos totales</p>
        <p style="font-size:2rem; font-weight:700; color:#28a745; margin:8px 0 0;">
            ${{ number_format($totalIngresos, 2) }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">en el período</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #17a2b8;">
        <p style="color:#999; font-size:0.82rem; margin:0;">💵 Efectivo</p>
        <p style="font-size:2rem; font-weight:700; color:#17a2b8; margin:8px 0 0;">
            ${{ number_format($totalEfectivo, 2) }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">cobrado</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:24px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #6f42c1;">
        <p style="color:#999; font-size:0.82rem; margin:0;">💳 Tarjeta</p>
        <p style="font-size:2rem; font-weight:700; color:#6f42c1; margin:8px 0 0;">
            ${{ number_format($totalTarjeta, 2) }}
        </p>
        <p style="color:#bbb; font-size:0.78rem; margin:4px 0 0;">cobrado</p>
    </div>

</div>

{{-- Gráfica + más vendidos --}}
<div style="display:grid; grid-template-columns:1fr 340px; gap:20px; margin-bottom:28px;">

    {{-- Gráfica --}}
    <div style="background:#fff; border-radius:12px; padding:24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.1rem; margin:0 0 20px;">
            📈 Ventas por día
        </h3>
        <canvas id="graficaVentas" height="120"></canvas>
    </div>

    {{-- Métodos de pago --}}
    <div style="background:#fff; border-radius:12px; padding:24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.1rem; margin:0 0 20px;">
            💳 Por método de pago
        </h3>
        <canvas id="graficaPagos" height="160"></canvas>
    </div>

</div>

{{-- Productos más vendidos + listado --}}
<div style="display:grid; grid-template-columns:340px 1fr; gap:20px; margin-bottom:28px;">

    {{-- Más vendidos --}}
    <div style="background:#fff; border-radius:12px; padding:24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.1rem; margin:0 0 16px;">
            🏆 Productos más vendidos
        </h3>
        @forelse($masVendidos as $index => $item)
        <div style="display:flex; align-items:center; gap:12px;
                    padding:10px 0; border-bottom:1px solid #f5f5f5;">
            <span style="width:24px; height:24px; border-radius:50%; background:#8B0000;
                         color:white; font-size:0.75rem; font-weight:700;
                         display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                {{ $index + 1 }}
            </span>
            <div style="flex:1;">
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
            Sin ventas en este período.
        </p>
        @endforelse
    </div>

    {{-- Listado de ventas --}}
    <div style="background:#fff; border-radius:12px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #f0f0f0;">
            <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                       font-size:1.1rem; margin:0;">
                Listado de ventas
            </h3>
        </div>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f9f9f9;">
                    <th style="padding:10px 16px; text-align:left; font-size:0.8rem; color:#999;">Folio</th>
                    <th style="padding:10px 16px; text-align:left; font-size:0.8rem; color:#999;">Fecha</th>
                    <th style="padding:10px 16px; text-align:left; font-size:0.8rem; color:#999;">Cajero</th>
                    <th style="padding:10px 16px; text-align:center; font-size:0.8rem; color:#999;">Método</th>
                    <th style="padding:10px 16px; text-align:right; font-size:0.8rem; color:#999;">Total</th>
                    <th style="padding:10px 16px; text-align:center; font-size:0.8rem; color:#999;">Ticket</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                <tr style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:12px 16px; font-weight:600; color:#8B0000; font-size:0.88rem;">
                        {{ $venta->folio }}
                    </td>
                    <td style="padding:12px 16px; color:#666; font-size:0.85rem;">
                        {{ $venta->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="padding:12px 16px; color:#666; font-size:0.85rem;">
                        {{ $venta->usuario->nombre ?? '—' }}
                    </td>
                    <td style="padding:12px 16px; text-align:center;">
                        @if($venta->metodo_pago === 'efectivo')
                            <span style="background:#d4edda; color:#155724; padding:3px 10px;
                                         border-radius:20px; font-size:0.78rem; font-weight:600;">
                                💵 Efectivo
                            </span>
                        @else
                            <span style="background:#cce5ff; color:#004085; padding:3px 10px;
                                         border-radius:20px; font-size:0.78rem; font-weight:600;">
                                💳 Tarjeta
                            </span>
                        @endif
                    </td>
                    <td style="padding:12px 16px; text-align:right; font-weight:700; color:#333; font-size:0.88rem;">
                        ${{ number_format($venta->total, 2) }}
                    </td>
                    <td style="padding:12px 16px; text-align:center;">
                        <a href="{{ route('ventas.ticket', $venta) }}"
                           style="color:#8B0000; font-size:0.82rem; font-weight:600; text-decoration:none;">
                            Ver →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:30px; text-align:center; color:#bbb; font-size:0.9rem;">
                        No hay ventas en este período.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($ventas->hasPages())
        <div style="padding:14px 16px; border-top:1px solid #f0f0f0;">
            {{ $ventas->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Scripts gráficas --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfica de línea
    new Chart(document.getElementById('graficaVentas').getContext('2d'), {
        type: 'line',
        data: {
            labels: {!! json_encode($graficaDias->pluck('fecha')) !!},
            datasets: [{
                label: 'Total',
                data: {!! json_encode($graficaDias->pluck('total')) !!},
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
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: val => '$' + val.toLocaleString('es-MX') }
                }
            }
        }
    });

    // Gráfica de barras métodos de pago
    new Chart(document.getElementById('graficaPagos').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Efectivo', 'Tarjeta'],
            datasets: [{
                data: [{{ $totalEfectivo }}, {{ $totalTarjeta }}],
                backgroundColor: ['rgba(23,162,184,0.8)', 'rgba(111,66,193,0.8)'],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: val => '$' + val.toLocaleString('es-MX') }
                }
            }
        }
    });
</script>

@endsection