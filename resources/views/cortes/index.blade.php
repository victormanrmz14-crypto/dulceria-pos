@extends('layouts.app')

@section('title', 'Historial de cortes')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            Historial de cortes
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            {{ auth()->user()->rol === 'admin' ? 'Cortes de todos los cajeros.' : 'Tus cortes de caja.' }}
        </p>
    </div>
    <div style="text-align:right;">
        <p style="color:#777; font-size:0.88rem; margin:0 0 6px;">
            Para hacer un nuevo corte ve al Dashboard
        </p>
        <a href="{{ route('dashboard') }}"
           style="background:#8B0000; color:#fff; padding:10px 20px; border-radius:8px;
                  text-decoration:none; font-weight:600; font-size:0.9rem;">
            → Ir al Dashboard
        </a>
    </div>
</div>

<div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#8B0000; color:#fff;">
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem; font-weight:600;">Fecha del corte</th>
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem; font-weight:600;">Período</th>
                @if(auth()->user()->rol === 'admin')
                <th style="padding:14px 20px; text-align:left; font-size:0.85rem; font-weight:600;">Cajero</th>
                @endif
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem; font-weight:600;">Trans.</th>
                <th style="padding:14px 20px; text-align:right; font-size:0.85rem; font-weight:600;">Efectivo</th>
                <th style="padding:14px 20px; text-align:right; font-size:0.85rem; font-weight:600;">Tarjeta</th>
                <th style="padding:14px 20px; text-align:right; font-size:0.85rem; font-weight:600;">Total</th>
                <th style="padding:14px 20px; text-align:center; font-size:0.85rem; font-weight:600;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($cortes as $corte)
            <tr style="border-bottom:1px solid #f0f0f0;">
                <td style="padding:14px 20px; font-size:0.9rem; font-weight:500;">
                    {{ $corte->fecha_corte->format('d/m/Y H:i') }}
                </td>
                <td style="padding:14px 20px; font-size:0.82rem; color:#777;">
                    {{ $corte->fecha_inicio->format('d/m H:i') }} → {{ $corte->fecha_corte->format('d/m H:i') }}
                </td>
                @if(auth()->user()->rol === 'admin')
                <td style="padding:14px 20px; font-size:0.9rem; color:#555;">
                    {{ $corte->usuario->nombre ?? '—' }}
                </td>
                @endif
                <td style="padding:14px 20px; text-align:center; font-size:0.9rem;">
                    {{ $corte->num_transacciones }}
                </td>
                <td style="padding:14px 20px; text-align:right; font-size:0.9rem; color:#17a2b8;">
                    ${{ number_format($corte->total_efectivo, 2) }}
                </td>
                <td style="padding:14px 20px; text-align:right; font-size:0.9rem; color:#6f42c1;">
                    ${{ number_format($corte->total_tarjeta, 2) }}
                </td>
                <td style="padding:14px 20px; text-align:right; font-weight:700; color:#8B0000; font-size:0.9rem;">
                    ${{ number_format($corte->total_general, 2) }}
                </td>
                <td style="padding:14px 20px; text-align:center;">
                    <a href="{{ route('cortes.show', $corte) }}"
                       style="background:#f0f0f0; color:#333; padding:5px 12px; border-radius:6px;
                              text-decoration:none; font-size:0.82rem; font-weight:500;">
                        Ver
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->rol === 'admin' ? 8 : 7 }}"
                    style="padding:40px; text-align:center; color:#aaa; font-size:0.9rem;">
                    No hay cortes registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($cortes->hasPages())
<div style="margin-top:20px;">
    {{ $cortes->links() }}
</div>
@endif

@endsection
