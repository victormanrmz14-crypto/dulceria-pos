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
        $query = Venta::with('usuario')->orderBy('created_at', 'desc');

        // El cajero solo ve sus propias ventas
        if (auth()->user()->rol !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        $ventas = $query->paginate(15);

        return view('ventas.historial', compact('ventas'));
    }

    // Ver ticket de una venta
    public function ticket(Venta $venta)
    {
        // El cajero solo puede ver tickets de sus propias ventas
        if (auth()->user()->rol !== 'admin' && $venta->user_id !== auth()->id()) {
            abort(403);
        }

        $venta->load(['detalles.producto', 'usuario']);
        return view('ventas.ticket', compact('venta'));
    }
}