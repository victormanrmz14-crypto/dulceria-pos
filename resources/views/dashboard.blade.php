@extends('layouts.app')

@section('title', 'Inicio')

@section('content')

<h2 style="font-family:'Playfair Display',serif; font-size:2rem;
           color:#8B0000; margin-bottom:6px;">
    Bienvenido, {{ Auth::user()->nombre }} 👋
</h2>
<p style="color:#999; margin-bottom:36px;">
    {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
</p>

<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:20px;">

    <div style="background:#fff; border-radius:12px; padding:28px 20px;
                text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <div style="font-size:2.2rem;">🛒</div>
        <p style="margin-top:10px; color:#777; font-size:0.85rem;">Ventas hoy</p>
        <p style="font-size:1.8rem; font-weight:700; color:#8B0000;">--</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:28px 20px;
                text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <div style="font-size:2.2rem;">💰</div>
        <p style="margin-top:10px; color:#777; font-size:0.85rem;">Total del día</p>
        <p style="font-size:1.8rem; font-weight:700; color:#8B0000;">--</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:28px 20px;
                text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <div style="font-size:2.2rem;">🍬</div>
        <p style="margin-top:10px; color:#777; font-size:0.85rem;">Productos activos</p>
        <p style="font-size:1.8rem; font-weight:700; color:#8B0000;">--</p>
    </div>

    <div style="background:#fff; border-radius:12px; padding:28px 20px;
                text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <div style="font-size:2.2rem;">👥</div>
        <p style="margin-top:10px; color:#777; font-size:0.85rem;">Usuarios</p>
        <p style="font-size:1.8rem; font-weight:700; color:#8B0000;">--</p>
    </div>

</div>

@endsection