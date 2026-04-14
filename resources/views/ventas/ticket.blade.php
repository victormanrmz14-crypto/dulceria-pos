@extends('layouts.app')

@section('title', 'Ticket - {{ $venta->folio }}')

@section('content')

<div style="max-width:500px; margin:0 auto;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            🧾 Ticket de Venta
        </h2>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()"
                    style="background:#8B0000; color:white; padding:10px 20px;
                           border:none; border-radius:8px; font-weight:600;
                           font-size:0.9rem; cursor:pointer;">
                🖨️ Imprimir
            </button>
            <a href="{{ route('ventas.index') }}"
               style="background:#f0f0f0; color:#555; padding:10px 20px;
                      border-radius:8px; text-decoration:none; font-weight:600;
                      font-size:0.9rem;">
                Nueva venta
            </a>
        </div>
    </div>

    <div style="background:#fff; border-radius:12px; padding:32px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);">

        {{-- Encabezado --}}
        <div style="text-align:center; margin-bottom:24px; padding-bottom:20px;
                    border-bottom:2px dashed #eee;">
            <h1 style="font-family:'Playfair Display',serif; color:#8B0000;
                       font-size:1.8rem; margin:0;">🍬 Dulcería POS</h1>
            <p style="color:#999; margin:4px 0 0; font-size:0.9rem;">
                {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>

        {{-- Info de la venta --}}
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <div>
                <p style="color:#999; font-size:0.8rem; margin:0;">Folio</p>
                <p style="font-weight:700; color:#8B0000; font-size:1.1rem; margin:4px 0 0;">
                    {{ $venta->folio }}
                </p>
            </div>
            <div style="text-align:right;">
                <p style="color:#999; font-size:0.8rem; margin:0;">Atendió</p>
                <p style="font-weight:600; margin:4px 0 0;">
                    {{ $venta->usuario->nombre }}
                </p>
            </div>
        </div>

        {{-- Productos --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <thead>
                <tr style="border-bottom:1px solid #eee;">
                    <th style="padding:8px 0; text-align:left; font-size:0.82rem; color:#999;">Producto</th>
                    <th style="padding:8px 0; text-align:center; font-size:0.82rem; color:#999;">Cant.</th>
                    <th style="padding:8px 0; text-align:right; font-size:0.82rem; color:#999;">Precio</th>
                    <th style="padding:8px 0; text-align:right; font-size:0.82rem; color:#999;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                <tr style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:10px 0; font-size:0.9rem;">
                        {{ $detalle->producto->nombre }}
                    </td>
                    <td style="padding:10px 0; text-align:center; font-size:0.9rem;">
                        {{ number_format($detalle->cantidad, 0) }}
                    </td>
                    <td style="padding:10px 0; text-align:right; font-size:0.9rem;">
                        ${{ number_format($detalle->precio_unitario, 2) }}
                    </td>
                    <td style="padding:10px 0; text-align:right; font-weight:600; font-size:0.9rem;">
                        ${{ number_format($detalle->importe, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totales --}}
        <div style="border-top:2px dashed #eee; padding-top:16px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                <span style="color:#777; font-size:0.9rem;">Subtotal</span>
                <span style="font-weight:600;">${{ number_format($venta->subtotal, 2) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <span style="color:#777; font-size:0.9rem;">IVA (16%)</span>
                <span style="font-weight:600;">${{ number_format($venta->impuestos, 2) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding-top:12px;
                        border-top:2px solid #eee;">
                <span style="font-weight:700; font-size:1.1rem;">Total</span>
                <span style="font-weight:700; font-size:1.3rem; color:#8B0000;">
                    ${{ number_format($venta->total, 2) }}
                </span>
            </div>
            <div style="margin-top:12px; text-align:right;">
                @if($venta->metodo_pago === 'efectivo')
                    <span style="background:#d4edda; color:#155724; padding:4px 12px;
                                 border-radius:20px; font-size:0.85rem; font-weight:600;">
                        💵 Efectivo
                    </span>
                @else
                    <span style="background:#cce5ff; color:#004085; padding:4px 12px;
                                 border-radius:20px; font-size:0.85rem; font-weight:600;">
                        💳 Tarjeta
                    </span>
                @endif
            </div>
        </div>

        {{-- Pie --}}
        <div style="text-align:center; margin-top:24px; padding-top:20px;
                    border-top:2px dashed #eee; color:#bbb; font-size:0.85rem;">
            <p style="margin:0;">¡Gracias por su compra!</p>
            <p style="margin:4px 0 0;">Dulcería POS</p>
        </div>
    </div>
</div>

@endsection