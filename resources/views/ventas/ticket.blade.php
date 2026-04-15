@extends('layouts.app')

@section('title', 'Ticket - ' . $venta->folio)

@section('content')

<style>
    /* ── Pantalla ── */
    .ticket-wrap {
        max-width: 400px;
        margin: 0 auto;
    }

    .ticket-body {
        background: #fff;
        border-radius: 12px;
        padding: 24px 28px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    /* ── Impresión ── */
    @media print {
        aside,
        .ticket-actions {
            display: none !important;
        }

        body {
            background: #fff !important;
            overflow: visible !important;
        }

        main {
            padding: 0 !important;
            overflow: visible !important;
        }

        .ticket-wrap {
            max-width: 100%;
            margin: 0;
        }

        .ticket-body {
            padding: 0;
            box-shadow: none !important;
            font-size: 11px;
        }

        table, th, td {
            font-size: 11px;
        }
    }
</style>

<div class="ticket-wrap">

    {{-- Cuerpo del ticket --}}
    <div class="ticket-body">

        {{-- Encabezado --}}
        <div style="text-align:center; margin-bottom:20px; padding-bottom:16px;
                    border-bottom:2px dashed #eee;">
            <h1 style="font-family:'Playfair Display',serif; color:#8B0000;
                       font-size:1.6rem; margin:0;">🍬 Dulcería POS</h1>
            <p style="color:#999; margin:4px 0 0; font-size:0.85rem;">
                {{ $venta->created_at->format('d/m/Y H:i') }}
            </p>
        </div>

        {{-- Info de la venta --}}
        <div style="display:flex; justify-content:space-between; margin-bottom:16px;">
            <div>
                <p style="color:#999; font-size:0.78rem; margin:0;">Folio</p>
                <p style="font-weight:700; color:#8B0000; font-size:1rem; margin:3px 0 0;">
                    {{ $venta->folio }}
                </p>
            </div>
            <div style="text-align:right;">
                <p style="color:#999; font-size:0.78rem; margin:0;">Atendió</p>
                <p style="font-weight:600; margin:3px 0 0; font-size:0.9rem;">
                    {{ $venta->usuario->nombre }}
                </p>
            </div>
        </div>

        {{-- Productos --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
            <thead>
                <tr style="border-bottom:1px solid #eee;">
                    <th style="padding:6px 0; text-align:left; font-size:0.78rem; color:#999; font-weight:600;">Producto</th>
                    <th style="padding:6px 0; text-align:center; font-size:0.78rem; color:#999; font-weight:600;">Cant.</th>
                    <th style="padding:6px 0; text-align:right; font-size:0.78rem; color:#999; font-weight:600;">Precio</th>
                    <th style="padding:6px 0; text-align:right; font-size:0.78rem; color:#999; font-weight:600;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                <tr style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:8px 0; font-size:0.88rem;">
                        {{ $detalle->producto->nombre }}
                    </td>
                    <td style="padding:8px 0; text-align:center; font-size:0.88rem;">
                        {{ number_format($detalle->cantidad, 0) }}
                    </td>
                    <td style="padding:8px 0; text-align:right; font-size:0.88rem;">
                        ${{ number_format($detalle->precio_unitario, 2) }}
                    </td>
                    <td style="padding:8px 0; text-align:right; font-weight:600; font-size:0.88rem;">
                        ${{ number_format($detalle->importe, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totales --}}
        <div style="border-top:2px dashed #eee; padding-top:14px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                <span style="color:#777; font-size:0.88rem;">Subtotal</span>
                <span style="font-weight:600; font-size:0.88rem;">${{ number_format($venta->subtotal, 2) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:#777; font-size:0.88rem;">IVA (16%)</span>
                <span style="font-weight:600; font-size:0.88rem;">${{ number_format($venta->impuestos, 2) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding-top:10px;
                        border-top:2px solid #eee;">
                <span style="font-weight:700; font-size:1.05rem;">Total</span>
                <span style="font-weight:700; font-size:1.2rem; color:#8B0000;">
                    ${{ number_format($venta->total, 2) }}
                </span>
            </div>

            @if($venta->monto_recibido !== null)
            <div style="display:flex; justify-content:space-between; margin-top:8px;">
                <span style="color:#777; font-size:0.88rem;">Recibido</span>
                <span style="font-weight:600; font-size:0.88rem;">${{ number_format($venta->monto_recibido, 2) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:3px;">
                <span style="color:#28a745; font-size:0.88rem; font-weight:600;">Cambio</span>
                <span style="font-weight:700; color:#28a745; font-size:0.88rem;">${{ number_format($venta->cambio, 2) }}</span>
            </div>
            @endif

            <div style="margin-top:10px; text-align:right;">
                @if($venta->metodo_pago === 'efectivo')
                    <span style="background:#d4edda; color:#155724; padding:3px 12px;
                                 border-radius:20px; font-size:0.82rem; font-weight:600;">
                        💵 Efectivo
                    </span>
                @else
                    <span style="background:#cce5ff; color:#004085; padding:3px 12px;
                                 border-radius:20px; font-size:0.82rem; font-weight:600;">
                        💳 Tarjeta
                    </span>
                @endif
            </div>
        </div>

        {{-- Pie --}}
        <div style="text-align:center; margin-top:20px; padding-top:16px;
                    border-top:2px dashed #eee; color:#bbb; font-size:0.82rem;">
            <p style="margin:0;">¡Gracias por su compra!</p>
            <p style="margin:4px 0 0;">Dulcería POS</p>
        </div>

    </div>
    {{-- Fin ticket-body --}}

    {{-- Botones debajo del ticket --}}
    <div class="ticket-actions"
         style="display:flex; gap:12px; justify-content:center; margin-top:24px;">
        <button onclick="window.print()"
                style="background:#8B0000; color:white; padding:10px 24px;
                       border:none; border-radius:8px; font-weight:600;
                       font-size:0.9rem; cursor:pointer;">
            🖨️ Imprimir
        </button>
        <a href="{{ route('ventas.index') }}"
           style="background:#f0f0f0; color:#555; padding:10px 24px;
                  border-radius:8px; text-decoration:none; font-weight:600;
                  font-size:0.9rem;">
            Nueva venta
        </a>
    </div>

</div>

@endsection
