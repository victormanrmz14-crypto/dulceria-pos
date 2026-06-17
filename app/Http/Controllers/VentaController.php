<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VentaController extends Controller
{
    // Pantalla principal del punto de venta
    public function index()
    {
        return Inertia::render('Ventas/Pos', [
            'productosIniciales' => $this->serializarProductos(
                Producto::where('activo', true)
                    ->where('stock', '>', 0)
                    ->orderBy('nombre')
                    ->limit(10)
                    ->get()
            ),
        ]);
    }

    // Búsqueda de productos para el POS (JSON)
    public function buscarProductos(Request $request)
    {
        $request->validate(['buscar' => 'nullable|string|max:100']);

        $productos = Producto::where('activo', true)
            ->where('stock', '>', 0)
            ->when($request->buscar, fn ($q) => $q->where('nombre', 'like', '%' . $request->buscar . '%'))
            ->orderBy('nombre')
            ->limit(10)
            ->get();

        return response()->json($this->serializarProductos($productos));
    }

    // Procesar la venta (lógica movida del componente Livewire PuntoVenta)
    public function store(Request $request)
    {
        $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.id'         => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad'   => ['required', 'integer', 'min:1'],
            'metodo_pago'        => ['required', 'in:efectivo,tarjeta'],
            'monto_recibido'     => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'total_cliente'      => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'El carrito está vacío.',
            'items.min'      => 'El carrito está vacío.',
        ]);

        try {
            $venta = DB::transaction(function () use ($request) {
                $subtotal = 0.0;
                $lineas   = [];

                // Verificar stock con lock pesimista y tomar el PRECIO REAL de la BD
                foreach ($request->items as $item) {
                    $producto = Producto::lockForUpdate()->find($item['id']);

                    if (!$producto || !$producto->activo) {
                        throw new \RuntimeException('Un producto del carrito ya no está disponible.');
                    }
                    if ($producto->stock < $item['cantidad']) {
                        throw new \RuntimeException("Stock insuficiente para {$producto->nombre}.");
                    }

                    $importe   = round($item['cantidad'] * (float) $producto->precio, 2);
                    $subtotal += $importe;

                    $lineas[] = [
                        'producto' => $producto,
                        'cantidad' => $item['cantidad'],
                        'precio'   => (float) $producto->precio,
                        'importe'  => $importe,
                    ];
                }

                $subtotal = round($subtotal, 2);
                $iva      = round($subtotal * 0.16, 2);
                $total    = round($subtotal + $iva, 2);

                // Si el total que vio el cajero ya no coincide (cambió un precio),
                // no cobrar un monto distinto al mostrado
                if (abs($total - (float) $request->total_cliente) >= 0.01) {
                    throw new \RuntimeException(
                        'Los precios cambiaron desde que armaste el carrito. Revisa el nuevo total antes de cobrar.'
                    );
                }

                $esEfectivo    = $request->metodo_pago === 'efectivo';
                $montoRecibido = $esEfectivo && (float) $request->monto_recibido > 0
                    ? round((float) $request->monto_recibido, 2)
                    : null;

                if ($montoRecibido !== null && $montoRecibido < $total) {
                    throw new \RuntimeException('El monto recibido es menor al total de la venta.');
                }

                $venta = Venta::create([
                    'user_id'        => auth()->id(),
                    'folio'          => null,
                    'metodo_pago'    => $request->metodo_pago,
                    'subtotal'       => $subtotal,
                    'impuestos'      => $iva,
                    'total'          => $total,
                    'monto_recibido' => $montoRecibido,
                    'cambio'         => $montoRecibido !== null ? round($montoRecibido - $total, 2) : null,
                ]);

                // Folio basado en el id real (sin race condition)
                $venta->folio = Venta::folioDesdeId($venta->id);
                $venta->save();

                foreach ($lineas as $linea) {
                    DetalleVenta::create([
                        'venta_id'        => $venta->id,
                        'producto_id'     => $linea['producto']->id,
                        'cantidad'        => $linea['cantidad'],
                        'precio_unitario' => $linea['precio'],
                        'importe'         => $linea['importe'],
                    ]);

                    Producto::where('id', $linea['producto']->id)
                        ->decrement('stock', $linea['cantidad']);
                }

                return $venta;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Ocurrió un error al procesar la venta. Inténtalo de nuevo.');
        }

        return redirect()->route('ventas.ticket', $venta)
            ->with('success', "Venta {$venta->folio} registrada correctamente.");
    }

    // Historial de ventas
    public function historial()
    {
        $query = Venta::with('usuario')->orderBy('created_at', 'desc');

        // El cajero solo ve sus propias ventas
        if (auth()->user()->rol !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        $ventas = $query->paginate(15)->through(fn ($v) => [
            'id'          => $v->id,
            'folio'       => $v->folio,
            'total'       => (float) $v->total,
            'metodo_pago' => $v->metodo_pago,
            'fecha'       => $v->created_at->isoFormat('DD/MM/YYYY HH:mm'),
            'cajero'      => $v->usuario->nombre ?? '—',
        ]);

        return Inertia::render('Ventas/Historial', ['ventas' => $ventas]);
    }

    // Ver ticket de una venta
    public function ticket(Venta $venta)
    {
        // El cajero solo puede ver tickets de sus propias ventas
        if (auth()->user()->rol !== 'admin' && $venta->user_id !== auth()->id()) {
            abort(403);
        }

        $venta->load(['detalles.producto', 'usuario']);

        return Inertia::render('Ventas/Ticket', [
            'venta' => [
                'id'             => $venta->id,
                'folio'          => $venta->folio,
                'fecha'          => $venta->created_at->format('d/m/Y H:i'),
                'cajero'         => $venta->usuario->nombre ?? '—',
                'metodo_pago'    => $venta->metodo_pago,
                'subtotal'       => (float) $venta->subtotal,
                'impuestos'      => (float) $venta->impuestos,
                'total'          => (float) $venta->total,
                'monto_recibido' => $venta->monto_recibido !== null ? (float) $venta->monto_recibido : null,
                'cambio'         => $venta->cambio !== null ? (float) $venta->cambio : null,
                'detalles'       => $venta->detalles->map(fn ($d) => [
                    'producto'        => $d->producto->nombre ?? '—',
                    'cantidad'        => (float) $d->cantidad,
                    'precio_unitario' => (float) $d->precio_unitario,
                    'importe'         => (float) $d->importe,
                ])->values(),
            ],
        ]);
    }

    private function serializarProductos($productos): array
    {
        return $productos->map(fn ($p) => [
            'id'            => $p->id,
            'nombre'        => $p->nombre,
            'precio'        => (float) $p->precio,
            'stock'         => (float) $p->stock,
            'unidad_medida' => $p->unidad_medida,
        ])->values()->all();
    }
}
