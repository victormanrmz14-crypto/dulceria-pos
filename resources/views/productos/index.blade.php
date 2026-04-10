@extends('layouts.app')

@section('title', 'Productos')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:#8B0000;">
            🍬 Productos
        </h2>
        <p style="color:#999; font-size:0.9rem; margin-top:4px;">
            Gestión de productos de la dulcería
        </p>
    </div>
    <a href="{{ route('productos.create') }}"
       style="background:#8B0000; color:white; padding:10px 22px; border-radius:8px;
              text-decoration:none; font-weight:600; font-size:0.9rem;">
        + Nuevo Producto
    </a>
</div>

@livewire('producto-index')

@endsection