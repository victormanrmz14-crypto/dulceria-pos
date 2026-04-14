@extends('layouts.app')

@section('title', 'Historial de Ventas')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            📋 Historial de Ventas
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Registro de todas las transacciones
        </p>
    </div>
    <a href="{{ route('ventas.index') }}"
       style="background:#8B0000; color:white; padding:10px 22px; border-radius:8px;
              text-decoration:none; font-weight:600; font-size:0.9rem;">
        + Nueva Venta
    </a>
</div>

<div style="background:#fff; border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#8B0000; color:white;">
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Folio</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Fecha</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem;">Cajero</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Método</th>
                <th style="padding:14px 20px; text-align:right; font-size:0.85rem;">Total</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr style="border-bottom:1px solid #f0f0f0;">
                <td style="padding:14px 20px; font-weight:600; color:#8B0000;">
                    {{ $venta->folio }}
                </td>
                <td style="padding:14px 20px; color:#666; font-size:0.9rem;">
                    {{ $venta->created_at->format('d/m/Y H:i') }}
                </td>
                <td style="padding:14px 20px; color:#666; font-size:0.9rem;">
                    {{ $venta->usuario->nombre ?? '—' }}
                </td>
                <td style="padding:14px 20px; text-align:center;">
                    @if($venta->metodo_pago === 'efectivo')
                        <span style="background:#d4edda; color:#155724; padding:4px 12px;
                                     border-radius:20px; font-size:0.8rem; font-weight:600;">
                            💵 Efectivo
                        </span>
                    @else
                        <span style="background:#cce5ff; color:#004085; padding:4px 12px;
                                     border-radius:20px; font-size:0.8rem; font-weight:600;">
                            💳 Tarjeta
                        </span>
                    @endif
                </td>
                <td style="padding:14px 20px; text-align:right; font-weight:700; color:#333;">
                    ${{ number_format($venta->total, 2) }}
                </td>
                <td style="padding:14px 20px; text-align:center;">
                    <a href="{{ route('ventas.ticket', $venta) }}"
                       style="background:#8B0000; color:white; padding:6px 14px;
                              border-radius:6px; text-decoration:none;
                              font-size:0.82rem; font-weight:600;">
                        Ver ticket
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:40px; text-align:center; color:#bbb;">
                    No hay ventas registradas.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($ventas->hasPages())
    <div style="padding:16px 20px; border-top:1px solid #f0f0f0;">
        {{ $ventas->links() }}
    </div>
    @endif
</div>

@endsection