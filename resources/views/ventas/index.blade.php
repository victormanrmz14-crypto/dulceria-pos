@extends('layouts.app')

@section('title', 'Punto de Venta')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            🛒 Punto de Venta
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
        </p>
    </div>
    @if(auth()->user()->rol === 'admin')
    <a href="{{ route('ventas.historial') }}"
       style="background:#f0f0f0; color:#555; padding:10px 22px; border-radius:8px;
              text-decoration:none; font-weight:600; font-size:0.9rem;">
        📋 Ver historial
    </a>
    @endif
</div>

@livewire('punto-venta')

@endsection