<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    // Pantalla principal de ventas
    public function index()
    {
        return view('ventas.index');
    }

    // Historial de ventas
    public function historial()
    {
        $ventas = Venta::with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('ventas.historial', compact('ventas'));
    }

    // Ver ticket de una venta
    public function ticket(Venta $venta)
    {
        $venta->load(['detalles.producto', 'usuario']);
        return view('ventas.ticket', compact('venta'));
    }
}