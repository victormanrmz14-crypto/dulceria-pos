@extends('layouts.app')

@section('title', 'Caja Actual')

@section('content')

<div x-data="{
    modalIngreso: false,
    modalRetiro:  false,
    modalCorte:   false,
    notas: '',
    efectivoContado: '',
    dineroEnCaja: '',
    montoRetiro: 0,
    errorRetiro: '',
    get errorRecuento() {
        return this.efectivoContado !== '' && parseFloat(this.efectivoContado) > {{ $efectivoEsperado }};
    },
    get errorDineroEnCaja() {
        return this.dineroEnCaja !== '' && this.efectivoContado !== '' &&
               parseFloat(this.dineroEnCaja) > parseFloat(this.efectivoContado);
    },
    get formInvalido() {
        return this.errorRecuento || this.errorDineroEnCaja;
    }
}">

{{-- ═══════ HEADER ═══════ --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000; margin:0;">
            🏦 Caja Actual
        </h2>
        <p style="color:#999; font-size:0.88rem; margin-top:4px;">
            {{ Auth::user()->nombre }} · {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
        </p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button @click="modalIngreso = true"
                style="background:#28a745; color:#fff; border:none; border-radius:10px;
                       padding:11px 20px; font-family:'DM Sans',sans-serif; font-weight:600;
                       font-size:0.9rem; cursor:pointer; transition:background 0.2s;">
            ↑ Ingresar Efectivo
        </button>
        <button @click="modalRetiro = true"
                style="background:#dc3545; color:#fff; border:none; border-radius:10px;
                       padding:11px 20px; font-family:'DM Sans',sans-serif; font-weight:600;
                       font-size:0.9rem; cursor:pointer; transition:background 0.2s;">
            ↓ Retirar Efectivo
        </button>
        <button @click="modalCorte = true"
                style="background:#fff; color:#8B0000; border:2px solid #8B0000;
                       border-radius:10px; padding:11px 20px; font-family:'DM Sans',sans-serif;
                       font-weight:700; font-size:0.9rem; cursor:pointer; transition:all 0.2s;">
            📋 Hacer Corte de Caja
        </button>
    </div>
</div>

{{-- ═══════ TABLA RESUMEN ═══════ --}}
<div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06);
            overflow:hidden; margin-bottom:28px;">
    <div style="padding:16px 24px; border-bottom:1px solid #f0f0f0;">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000; font-size:1.1rem; margin:0;">
            Resumen del turno
        </h3>
        @if($ultimoCorte)
        <p style="color:#aaa; font-size:0.78rem; margin:4px 0 0;">
            Desde el último corte: {{ $ultimoCorte->fecha_corte->isoFormat('D MMM [a las] HH:mm') }}
        </p>
        @else
        <p style="color:#aaa; font-size:0.78rem; margin:4px 0 0;">
            Desde el inicio del día
        </p>
        @endif
    </div>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f9f9f9; border-bottom:2px solid #eee;">
                <th style="padding:12px 24px; text-align:left; color:#777; font-size:0.82rem; font-weight:600;">Método</th>
                <th style="padding:12px 24px; text-align:right; color:#777; font-size:0.82rem; font-weight:600;">Total esperado</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom:1px solid #f5f5f5;">
                <td style="padding:16px 24px;">
                    <span style="display:inline-flex; align-items:center; gap:10px;">
                        <span style="width:10px; height:10px; border-radius:50%;
                                     background:#17a2b8; display:inline-block;"></span>
                        <span style="font-weight:600; color:#333;">💵 Efectivo</span>
                    </span>
                    @if($ingresos > 0 || $retiros > 0)
                    <p style="color:#aaa; font-size:0.75rem; margin:4px 0 0 20px;">
                        Ventas + ${{ number_format($ingresos, 2) }} ingresos − ${{ number_format($retiros, 2) }} retiros
                    </p>
                    @endif
                </td>
                <td style="padding:16px 24px; text-align:right; font-size:1.3rem;
                            font-weight:700; color:#17a2b8;">
                    ${{ number_format($efectivoEsperado, 2) }}
                </td>
            </tr>
            <tr style="border-bottom:1px solid #f5f5f5;">
                <td style="padding:16px 24px;">
                    <span style="display:inline-flex; align-items:center; gap:10px;">
                        <span style="width:10px; height:10px; border-radius:50%;
                                     background:#6f42c1; display:inline-block;"></span>
                        <span style="font-weight:600; color:#333;">💳 Tarjeta</span>
                    </span>
                </td>
                <td style="padding:16px 24px; text-align:right; font-size:1.3rem;
                            font-weight:700; color:#6f42c1;">
                    ${{ number_format($tarjetaEsperada, 2) }}
                </td>
            </tr>
            <tr style="background:#fff5f5;">
                <td style="padding:16px 24px; font-weight:700; color:#333; font-size:0.95rem;">
                    Total del turno
                </td>
                <td style="padding:16px 24px; text-align:right; font-size:1.4rem;
                            font-weight:700; color:#8B0000;">
                    ${{ number_format($efectivoEsperado + $tarjetaEsperada, 2) }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ═══════ HISTORIAL DE MOVIMIENTOS ═══════ --}}
<div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <div style="padding:16px 24px; border-bottom:1px solid #f0f0f0;">
        <h3 style="font-family:'Playfair Display',serif; color:#8B0000; font-size:1.1rem; margin:0;">
            Movimientos del turno
        </h3>
    </div>
    @if($movimientos->isEmpty())
    <div style="padding:40px; text-align:center; color:#bbb;">
        <p style="font-size:1.5rem; margin:0;">💸</p>
        <p style="font-size:0.9rem; margin:8px 0 0;">Sin movimientos registrados en este turno.</p>
    </div>
    @else
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f9f9f9; border-bottom:2px solid #eee;">
                <th style="padding:11px 20px; text-align:left; color:#777; font-size:0.82rem; font-weight:600;">Tipo</th>
                <th style="padding:11px 20px; text-align:right; color:#777; font-size:0.82rem; font-weight:600;">Monto</th>
                <th style="padding:11px 20px; text-align:left; color:#777; font-size:0.82rem; font-weight:600;">Motivo</th>
                <th style="padding:11px 20px; text-align:left; color:#777; font-size:0.82rem; font-weight:600;">Usuario</th>
                <th style="padding:11px 20px; text-align:right; color:#777; font-size:0.82rem; font-weight:600;">Hora</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $mov)
            <tr style="border-bottom:1px solid #f5f5f5;">
                <td style="padding:13px 20px;">
                    @if($mov->tipo === 'ingreso')
                    <span style="background:#d4edda; color:#155724; padding:3px 12px;
                                 border-radius:20px; font-size:0.8rem; font-weight:600;">
                        ↑ Ingreso
                    </span>
                    @else
                    <span style="background:#f8d7da; color:#721c24; padding:3px 12px;
                                 border-radius:20px; font-size:0.8rem; font-weight:600;">
                        ↓ Retiro
                    </span>
                    @endif
                </td>
                <td style="padding:13px 20px; text-align:right; font-weight:700;
                            color:{{ $mov->tipo === 'ingreso' ? '#28a745' : '#dc3545' }};">
                    {{ $mov->tipo === 'ingreso' ? '+' : '−' }}${{ number_format($mov->monto, 2) }}
                </td>
                <td style="padding:13px 20px; color:#555; font-size:0.9rem;">
                    {{ $mov->motivo ?? '—' }}
                </td>
                <td style="padding:13px 20px; color:#666; font-size:0.88rem;">
                    {{ $mov->usuario->nombre ?? '—' }}
                </td>
                <td style="padding:13px 20px; text-align:right; color:#aaa; font-size:0.85rem;">
                    {{ $mov->created_at->format('H:i') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ═══════════════ MODAL INGRESO ═══════════════ --}}
<div x-show="modalIngreso"
     x-transition.opacity
     style="position:fixed; inset:0; background:rgba(0,0,0,0.5);
            display:flex; align-items:center; justify-content:center; z-index:1000;
            font-family:'DM Sans',sans-serif;"
     @keydown.escape.window="modalIngreso = false">
    <div style="background:#fff; border-radius:16px; padding:32px; width:100%;
                max-width:440px; box-shadow:0 24px 64px rgba(0,0,0,0.25); margin:16px;"
         @click.stop>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="font-family:'Playfair Display',serif; color:#28a745; font-size:1.4rem; margin:0;">
                ↑ Ingresar Efectivo
            </h3>
            <button @click="modalIngreso = false"
                    style="background:none; border:none; font-size:1.2rem; cursor:pointer;
                           color:#aaa; line-height:1; padding:4px 8px;">✕</button>
        </div>

        <form method="POST" action="{{ route('caja.ingreso') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.85rem; font-weight:600;
                              color:#555; margin-bottom:6px;">Monto *</label>
                <input type="number" name="monto" min="0.01" step="0.01"
                       placeholder="0.00" required
                       style="width:100%; padding:11px 14px; border:1px solid #ddd;
                              border-radius:10px; font-size:1rem; outline:none;
                              font-family:inherit; -moz-appearance:textfield;">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.85rem; font-weight:600;
                              color:#555; margin-bottom:6px;">
                    Motivo <span style="font-weight:400; color:#aaa;">(opcional)</span>
                </label>
                <input type="text" name="motivo" placeholder="Ej. Fondo inicial, Cambio..."
                       maxlength="255"
                       style="width:100%; padding:11px 14px; border:1px solid #ddd;
                              border-radius:10px; font-size:0.9rem; outline:none;
                              font-family:inherit;">
            </div>
            <p style="font-size:0.82rem; color:#aaa; margin-bottom:20px;">
                Realizado por: <strong style="color:#555;">{{ Auth::user()->nombre }}</strong>
            </p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button type="button" @click="modalIngreso = false"
                        style="padding:12px; background:#f0f0f0; color:#555; border:none;
                               border-radius:10px; font-weight:600; font-size:0.9rem;
                               cursor:pointer; font-family:inherit;">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding:12px; background:#28a745; color:#fff; border:none;
                               border-radius:10px; font-weight:700; font-size:0.9rem;
                               cursor:pointer; font-family:inherit;">
                    ✅ Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════ MODAL RETIRO ═══════════════ --}}
<div x-show="modalRetiro"
     x-transition.opacity
     style="position:fixed; inset:0; background:rgba(0,0,0,0.5);
            display:flex; align-items:center; justify-content:center; z-index:1000;
            font-family:'DM Sans',sans-serif;"
     @keydown.escape.window="modalRetiro = false">
    <div style="background:#fff; border-radius:16px; padding:32px; width:100%;
                max-width:440px; box-shadow:0 24px 64px rgba(0,0,0,0.25); margin:16px;"
         @click.stop>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="font-family:'Playfair Display',serif; color:#dc3545; font-size:1.4rem; margin:0;">
                ↓ Retirar Efectivo
            </h3>
            <button @click="modalRetiro = false"
                    style="background:none; border:none; font-size:1.2rem; cursor:pointer;
                           color:#aaa; line-height:1; padding:4px 8px;">✕</button>
        </div>
        <p style="background:#fff3cd; color:#856404; padding:10px 14px; border-radius:8px;
                  font-size:0.82rem; margin-bottom:20px;">
            Efectivo disponible:
            <strong>${{ number_format($efectivoEsperado, 2) }}</strong>
        </p>

        <form method="POST" action="{{ route('caja.retiro') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.85rem; font-weight:600;
                              color:#555; margin-bottom:6px;">Monto *</label>
                <input type="number" name="monto" min="0.01" step="0.01"
                       placeholder="0.00" required
                       x-model="montoRetiro"
                       @input="errorRetiro = montoRetiro > {{ $efectivoEsperado }}
                           ? 'El monto no puede ser mayor al efectivo disponible (${{ number_format($efectivoEsperado, 2) }})'
                           : ''"
                       style="width:100%; padding:11px 14px; border:1px solid #ddd;
                              border-radius:10px; font-size:1rem; outline:none;
                              font-family:inherit; -moz-appearance:textfield;">
                <p x-show="errorRetiro" x-text="errorRetiro"
                   style="color:#dc3545; font-size:0.82rem; margin-top:4px;"></p>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.85rem; font-weight:600;
                              color:#555; margin-bottom:6px;">
                    Motivo <span style="font-weight:400; color:#aaa;">(opcional)</span>
                </label>
                <input type="text" name="motivo" placeholder="Ej. Pago proveedor, Gastos..."
                       maxlength="255"
                       style="width:100%; padding:11px 14px; border:1px solid #ddd;
                              border-radius:10px; font-size:0.9rem; outline:none;
                              font-family:inherit;">
            </div>
            <p style="font-size:0.82rem; color:#aaa; margin-bottom:20px;">
                Realizado por: <strong style="color:#555;">{{ Auth::user()->nombre }}</strong>
            </p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button type="button" @click="modalRetiro = false"
                        style="padding:12px; background:#f0f0f0; color:#555; border:none;
                               border-radius:10px; font-weight:600; font-size:0.9rem;
                               cursor:pointer; font-family:inherit;">
                    Cancelar
                </button>
                <button type="submit"
                        :disabled="errorRetiro !== '' || montoRetiro <= 0"
                        :style="(errorRetiro !== '' || montoRetiro <= 0)
                            ? 'padding:12px;background:#ccc;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:not-allowed;font-family:inherit;'
                            : 'padding:12px;background:#dc3545;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;font-family:inherit;'">
                    ✅ Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════ MODAL CORTE DE CAJA ═══════════════ --}}
<div x-show="modalCorte"
     x-transition.opacity
     style="position:fixed; inset:0; background:rgba(0,0,0,0.5);
            display:flex; align-items:center; justify-content:center; z-index:1000;
            font-family:'DM Sans',sans-serif;"
     @keydown.escape.window="modalCorte = false">
    <div style="background:#fff; border-radius:16px; padding:32px; width:100%;
                max-width:560px; box-shadow:0 24px 64px rgba(0,0,0,0.25); margin:16px;"
         @click.stop>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="font-family:'Playfair Display',serif; color:#8B0000; font-size:1.4rem; margin:0;">
                📋 Corte de Caja
            </h3>
            <button @click="modalCorte = false; notas = ''; efectivoContado = ''; dineroEnCaja = ''"
                    style="background:none; border:none; font-size:1.2rem;
                           cursor:pointer; color:#aaa; line-height:1; padding:4px 8px;">✕</button>
        </div>

        <table style="width:100%; border-collapse:collapse; font-size:0.88rem; margin-bottom:0;">
            <thead>
                <tr style="background:#f9f9f9; border-bottom:2px solid #eee;">
                    <th style="padding:10px 14px; text-align:left; color:#777; font-weight:600; font-size:0.8rem;">Método</th>
                    <th style="padding:10px 14px; text-align:right; color:#777; font-weight:600; font-size:0.8rem;">Total esperado</th>
                    <th style="padding:10px 14px; text-align:right; color:#777; font-weight:600; font-size:0.8rem;">Recuento manual</th>
                    <th style="padding:10px 14px; text-align:right; color:#777; font-weight:600; font-size:0.8rem;">Diferencia</th>
                </tr>
            </thead>
            <tbody>
                {{-- Efectivo --}}
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:12px 14px;">
                        <span style="display:inline-flex; align-items:center; gap:8px;">
                            <span style="width:10px; height:10px; border-radius:50%;
                                         background:#17a2b8; display:inline-block;"></span>
                            💵 Efectivo
                        </span>
                    </td>
                    <td style="padding:12px 14px; text-align:right; font-weight:600; color:#17a2b8;">
                        ${{ number_format($efectivoEsperado, 2) }}
                    </td>
                    <td style="padding:12px 14px; text-align:right;">
                        <input type="number" x-model="efectivoContado"
                               min="0" step="0.01" placeholder="0.00"
                               :style="errorRecuento
                                   ? 'width:100px;padding:6px 10px;border:1.5px solid #dc3545;border-radius:8px;font-size:0.88rem;text-align:right;outline:none;font-family:inherit;-moz-appearance:textfield;'
                                   : 'width:100px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:0.88rem;text-align:right;outline:none;font-family:inherit;-moz-appearance:textfield;'">
                        <p x-show="errorRecuento"
                           style="color:#dc3545; font-size:0.72rem; margin:5px 0 0;
                                  text-align:right; line-height:1.3; max-width:130px; margin-left:auto;">
                            El recuento no puede superar el total esperado
                        </p>
                    </td>
                    <td style="padding:12px 14px; text-align:right; font-weight:600;"
                        :style="efectivoContado === '' ? 'color:#bbb;' : (parseFloat(efectivoContado) < {{ $efectivoEsperado }} ? 'color:#dc3545;' : 'color:#28a745;')"
                        x-text="efectivoContado === '' ? '—' : '$' + (parseFloat(efectivoContado) - {{ $efectivoEsperado }}).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2})">
                    </td>
                </tr>
                {{-- Tarjeta --}}
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:12px 14px;">
                        <span style="display:inline-flex; align-items:center; gap:8px;">
                            <span style="width:10px; height:10px; border-radius:50%;
                                         background:#6f42c1; display:inline-block;"></span>
                            💳 Tarjeta
                        </span>
                    </td>
                    <td style="padding:12px 14px; text-align:right; font-weight:600; color:#6f42c1;">
                        ${{ number_format($tarjetaEsperada, 2) }}
                    </td>
                    <td style="padding:12px 14px; text-align:right; color:#bbb; font-size:0.82rem;">
                        Sin recuento
                    </td>
                    <td style="padding:12px 14px; text-align:right; color:#bbb; font-size:0.82rem;">
                        —
                    </td>
                </tr>
            </tbody>
        </table>

        <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">

        <div style="margin-bottom:8px;">
            <label style="display:block; font-size:0.85rem; font-weight:600; color:#555; margin-bottom:8px;">
                ¿Cuánto dinero dejas en caja?
            </label>
            <input type="number" x-model="dineroEnCaja"
                   min="0" step="0.01" placeholder="0.00"
                   :style="errorDineroEnCaja
                       ? 'width:100%;padding:10px 12px;border:1.5px solid #dc3545;border-radius:8px;font-size:0.9rem;outline:none;font-family:inherit;box-sizing:border-box;-moz-appearance:textfield;'
                       : 'width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:0.9rem;outline:none;font-family:inherit;box-sizing:border-box;-moz-appearance:textfield;'">
            <p x-show="errorDineroEnCaja"
               style="color:#dc3545; font-size:0.78rem; margin:5px 0 0;">
                No puedes dejar más dinero del que contaste
            </p>
            <p x-show="!errorDineroEnCaja"
               style="color:#aaa; font-size:0.78rem; margin:6px 0 0;">
                El resto se considera retirado.
            </p>
        </div>

        <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">

        <div style="display:flex; justify-content:space-between; align-items:center;
                    background:#fff5f5; border-radius:10px; padding:14px 16px; margin-bottom:20px;">
            <span style="font-weight:700; color:#333; font-size:0.95rem;">Total general del turno</span>
            <span style="font-weight:700; font-size:1.15rem; color:#8B0000;">
                ${{ number_format($efectivoEsperado + $tarjetaEsperada, 2) }}
            </span>
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block; font-size:0.85rem; font-weight:600; color:#555; margin-bottom:6px;">
                Notas <span style="font-weight:400; color:#aaa;">(opcional)</span>
            </label>
            <textarea x-model="notas" rows="2"
                      placeholder="Ej. Turno tarde, observaciones..."
                      style="width:100%; padding:10px 12px; border:1px solid #ddd;
                             border-radius:8px; font-size:0.9rem; outline:none;
                             resize:none; font-family:inherit; box-sizing:border-box;"></textarea>
        </div>

        <form method="POST" action="{{ route('cortes.store') }}">
            @csrf
            <input type="hidden" name="notas"            :value="notas">
            <input type="hidden" name="efectivo_contado" :value="efectivoContado">
            <input type="hidden" name="dinero_en_caja"   :value="dineroEnCaja">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button type="button"
                        @click="modalCorte = false; notas = ''; efectivoContado = ''; dineroEnCaja = ''"
                        style="padding:13px; background:#f0f0f0; color:#555; border:none;
                               border-radius:10px; font-weight:600; font-size:0.9rem;
                               cursor:pointer; font-family:inherit;">
                    Cancelar
                </button>
                <button type="submit"
                        :disabled="formInvalido"
                        :style="formInvalido
                            ? 'padding:13px;background:#ccc;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:not-allowed;font-family:inherit;'
                            : 'padding:13px;background:#8B0000;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;font-family:inherit;'">
                    ✅ Confirmar corte
                </button>
            </div>
        </form>

    </div>
</div>

</div>{{-- cierre x-data --}}

@endsection
