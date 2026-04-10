<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Categoria;
use Livewire\Component;

class ProductoIndex extends Component
{
    public array $productos = [];
    public $categorias;

    public function mount(): void
    {
        $this->categorias = Categoria::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $this->loadProductos();
    }

    public function toggleActivo(Producto $producto): void
    {
        $producto->update(['activo' => !$producto->activo]);
        $this->loadProductos();
    }

    private function loadProductos(): void
    {
        $this->productos = Producto::query()
            ->select([
                'id',
                'categoria_id',
                'marca_id',
                'nombre',
                'precio',
                'stock',
                'stock_minimo',
                'unidad_medida',
                'activo',
            ])
            ->with([
                'categoria:id,nombre',
                'marca:id,nombre',
            ])
            ->orderBy('nombre')
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'categoria_id' => $producto->categoria_id,
                    'nombre' => $producto->nombre,
                    'categoria' => $producto->categoria?->nombre,
                    'marca' => $producto->marca?->nombre,
                    'precio' => (float) $producto->precio,
                    'stock' => (float) $producto->stock,
                    'stock_minimo' => (float) $producto->stock_minimo,
                    'unidad_medida' => $producto->unidad_medida,
                    'activo' => (bool) $producto->activo,
                ];
            })
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.producto-index');
    }
}