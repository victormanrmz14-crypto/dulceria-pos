@extends('layouts.app')

@section('title', 'Detalle de corte')

@section('content')

<div style="margin-bottom:28px; display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            Corte #{{ $corte->id }}
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Generado el {{ $corte->fecha_corte->isoFormat('dddd D [de] MMMM [de] YYYY [a las] HH:mm') }}
        </p>
    </div>
    <a href="{{ route('cortes.index') }}"
       style="background:#f0f0f0; color:#555; padding:10px 18px; border-radius:8px;
              text-decoration:none; font-weight:600; font-size:0.88rem;">
        ← Volver
    </a>
</div>

{{-- Resumen del corte --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr));
            gap:16px; margin-bottom:28px;">

    <div style="background:#fff; border-radius:12px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #8B0000;">
        <p style="color:#999; font-size:0.8rem; margin:0;">Transacciones</p>
        <p style="font-size:1.8rem; font-weight:700; color:#8B0000; margin:6px 0 0;">
            {{ $corte->num_transacciones }}
        </p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #17a2b8;">
        <p style="color:#999; font-size:0.8rem; margin:0;">💵 Efectivo</p>
        <p style="font-size:1.8rem; font-weight:700; color:#17a2b8; margin:6px 0 0;">
            ${{ number_format($corte->total_efectivo, 2) }}
        </p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #6f42c1;">
        <p style="color:#999; font-size:0.8rem; margin:0;">💳 Tarjeta</p>
        <p style="font-size:1.8rem; font-weight:700; color:#6f42c1; margin:6px 0 0;">
            ${{ number_format($corte->total_tarjeta, 2) }}
        </p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #28a745;">
        <p style="color:#999; font-size:0.8rem; margin:0;">Total general</p>
        <p style="font-size:1.8rem; font-weight:700; color:#28a745; margin:6px 0 0;">
            ${{ number_format($corte->total_general, 2) }}
        </p>
    </div>
</div>

{{-- Información del corte --}}
<div style="background:#fff; border-radius:12px; padding:24px;
            box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:24px;">
    <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
               font-size:1.1rem; margin:0 0 16px;">
        Información del corte
    </h3>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; font-size:0.9rem;">
        <div>
            <p style="color:#999; margin:0 0 4px; font-size:0.8rem;">Cajero</p>
            <p style="font-weight:600; margin:0;">{{ $corte->usuario->nombre ?? '—' }}</p>
        </div>
        <div>
            <p style="color:#999; margin:0 0 4px; font-size:0.8rem;">Fecha del corte</p>
            <p style="font-weight:600; margin:0;">{{ $corte->fecha_corte->format('d/m/Y H:i:s') }}</p>
        </div>
        <div>
            <p style="color:#999; margin:0 0 4px; font-size:0.8rem;">Inicio del período</p>
            <p style="font-weight:600; margin:0;">{{ $corte->fecha_inicio->format('d/m/Y H:i:s') }}</p>
        </div>
        <div>
            <p style="color:#999; margin:0 0 4px; font-size:0.8rem;">Fin del período</p>
            <p style="font-weight:600; margin:0;">{{ $corte->fecha_corte->format('d/m/Y H:i:s') }}</p>
        </div>
        @if($corte->notas)
        <div style="grid-column:1/-1;">
            <p style="color:#999; margin:0 0 4px; font-size:0.8rem;">Notas</p>
            <p style="margin:0;">{{ $corte->notas }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Ventas incluidas --}}
<div style="background:#fff; border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <div style="padding:16px 20px; border-bottom:1px solid #f0f0f0;">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000;
                   font-size:1.1rem; margin:0;">
            Ventas incluidas ({{ $ventas->count() }})
        </h3>
    </div>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#fafafa; border-bottom:1px solid #eee;">
                <th style="padding:12px 20px; text-align:left; font-size:0.82rem;
                           font-weight:600; color:#777;">Folio</th>
                <th style="padding:12px 20px; text-align:left; font-size:0.82rem;
                           font-weight:600; color:#777;">Hora</th>
                <th style="padding:12px 20px; text-align:center; font-size:0.82rem;
                           font-weight:600; color:#777;">Método</th>
                <th style="padding:12px 20px; text-align:right; font-size:0.82rem;
                           font-weight:600; color:#777;">Total</th>
                <th style="padding:12px 20px; text-align:center; font-size:0.82rem;
                           font-weight:600; color:#777;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr style="border-bottom:1px solid #f5f5f5;">
                <td style="padding:12px 20px; font-weight:600; font-size:0.9rem; color:#333;">
                    {{ $venta->folio }}
                </td>
                <td style="padding:12px 20px; font-size:0.88rem; color:#777;">
                    {{ $venta->created_at->format('d/m/Y H:i') }}
                </td>
                <td style="padding:12px 20px; text-align:center;">
                    @if($venta->metodo_pago === 'efectivo')
                        <span style="background:#e8f5e9; color:#2e7d32; padding:3px 10px;
                                     border-radius:20px; font-size:0.78rem; font-weight:600;">
                            💵 Efectivo
                        </span>
                    @else
                        <span style="background:#f3e5f5; color:#6a1b9a; padding:3px 10px;
                                     border-radius:20px; font-size:0.78rem; font-weight:600;">
                            💳 Tarjeta
                        </span>
                    @endif
                </td>
                <td style="padding:12px 20px; text-align:right; font-weight:700;
                           color:#8B0000; font-size:0.9rem;">
                    ${{ number_format($venta->total, 2) }}
                </td>
                <td style="padding:12px 20px; text-align:center;">
                    <a href="{{ route('ventas.ticket', $venta) }}"
                       style="color:#8B0000; font-size:0.82rem; font-weight:500;
                              text-decoration:none;">
                        Ticket
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:32px; text-align:center;
                                        color:#aaa; font-size:0.9rem;">
                    No se registraron ventas en este período.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
