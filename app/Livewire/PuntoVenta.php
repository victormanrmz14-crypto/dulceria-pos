<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PuntoVenta extends Component
{
    // Búsqueda
    public string $buscar = '';

    // Carrito
    public array $carrito = [];

    // Pago
    public string $metodoPago    = 'efectivo';
    public ?float $montoRecibido = null;

    // Modal confirmación
    public bool $mostrarConfirmacion = false;
    
    public function getProductosProperty()
    {
        return Producto::where('activo', true)
            ->where('stock', '>', 0)
            ->when($this->buscar, function ($q) {
                $q->where('nombre', 'like', '%' . $this->buscar . '%');
                })
                ->orderBy('nombre')
                ->limit(10)
                ->get();
    }

    // Agregar producto al carrito
    public function agregarProducto(int $id): void
    {
        $producto = Producto::find($id);
        if (!$producto || !$producto->activo || $producto->stock <= 0) return;

        if (isset($this->carrito[$id])) {
            // Verificar stock disponible
            if ($this->carrito[$id]['cantidad'] >= $producto->stock) return;
            $this->carrito[$id]['cantidad']++;
            $this->carrito[$id]['importe'] = round(
                $this->carrito[$id]['cantidad'] * $this->carrito[$id]['precio'], 2
            );
        } else {
            $this->carrito[$id] = [
                'id'       => $producto->id,
                'nombre'   => $producto->nombre,
                'precio'   => $producto->precio,
                'cantidad' => 1,
                'importe'  => $producto->precio,
                'stock'    => $producto->stock,
                'unidad'   => $producto->unidad_medida,
            ];
        }

        $this->buscar = '';
    }

    // Quitar producto del carrito
    public function quitarProducto(int $id): void
    {
        unset($this->carrito[$id]);
    }

    // Cambiar cantidad
    public function cambiarCantidad(int $id, mixed $cantidad): void
    {
        $cantidad = (int) $cantidad;
        if ($cantidad <= 0) {
            $this->quitarProducto($id);
            return;
        }

        $producto = Producto::find($id);
        if (!$producto) return;
        if ($cantidad > (int) $producto->stock) {
            $cantidad = (int) $producto->stock;
        }

        $this->carrito[$id]['cantidad'] = $cantidad;
        $this->carrito[$id]['importe']  = round($cantidad * $this->carrito[$id]['precio'], 2);
    }

    // Calcular subtotal
    public function getSubtotalProperty(): float
    {
        return round(array_sum(array_column($this->carrito, 'importe')), 2);
    }

    // Calcular IVA
    public function getIvaProperty(): float
    {
        return round($this->subtotal * 0.16, 2);
    }

    // Calcular total
    public function getTotalProperty(): float
    {
        return round($this->subtotal + $this->iva, 2);
    }

    // Calcular cambio
    public function getCambioProperty(): float
    {
        if (!$this->montoRecibido || $this->montoRecibido <= 0) return 0;
        if ($this->metodoPago === 'efectivo' && $this->montoRecibido >= $this->total) {
            return round($this->montoRecibido - $this->total, 2);
        }
        return 0;
    }

    // Confirmar venta
    public function confirmarVenta(): void
    {
        if (empty($this->carrito)) return;
        $this->mostrarConfirmacion = true;
    }

    // Procesar venta
    public function procesarVenta(): void
    {
        if (empty($this->carrito)) return;

        // Validar monto recibido solo si el cajero capturó uno
        if ($this->metodoPago === 'efectivo'
            && $this->montoRecibido !== null && $this->montoRecibido > 0
            && $this->montoRecibido < $this->total) {
            session()->flash('error', 'El monto recibido es menor al total de la venta.');
            return;
        }

        try {
        $venta = DB::transaction(function () {
            // Re-verificar stock dentro de la transacción con lock pesimista
            foreach ($this->carrito as $item) {
                $producto = Producto::lockForUpdate()->find($item['id']);
                if (!$producto || $producto->stock < $item['cantidad']) {
                    throw new \RuntimeException("Stock insuficiente para {$item['nombre']}.");
                }
            }

            // Crear venta sin folio (null no viola UNIQUE), se asigna tras conocer el id
            $esEfectivo      = $this->metodoPago === 'efectivo';
            $montoRecibido   = $esEfectivo && $this->montoRecibido > 0 ? $this->montoRecibido : null;
            $cambio          = $montoRecibido !== null ? $this->cambio : null;

            $venta = Venta::create([
                'user_id'        => auth()->id(),
                'folio'          => null,
                'metodo_pago'    => $this->metodoPago,
                'subtotal'       => $this->subtotal,
                'impuestos'      => $this->iva,
                'total'          => $this->total,
                'monto_recibido' => $montoRecibido,
                'cambio'         => $cambio,
            ]);

            // Asignar folio basado en el id real (sin race condition)
            $venta->folio = Venta::folioDesdeId($venta->id);
            $venta->save();

            // Crear detalles y descontar stock
            foreach ($this->carrito as $item) {
                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $item['id'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'importe'         => $item['importe'],
                ]);

                Producto::where('id', $item['id'])
                    ->decrement('stock', $item['cantidad']);
            }

            return $venta;
        });

        // Limpiar carrito
        $this->carrito             = [];
        $this->buscar              = '';
        $this->metodoPago          = 'efectivo';
        $this->montoRecibido       = null;
        $this->mostrarConfirmacion = false;

        session()->flash('success', "Venta {$venta->folio} registrada correctamente.");

        // Redirigir al ticket
        $this->redirect(route('ventas.ticket', $venta->id));

        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            $this->mostrarConfirmacion = false;
        }
    }

    // Cancelar confirmación
    public function cancelarConfirmacion(): void
    {
        $this->mostrarConfirmacion = false;
    }

    // Limpiar carrito
    public function limpiarCarrito(): void
    {
        $this->carrito       = [];
        $this->buscar        = '';
        $this->metodoPago    = 'efectivo';
        $this->montoRecibido = 0;
    }

    public function render()
    {
        return view('livewire.punto-venta');
    }
}