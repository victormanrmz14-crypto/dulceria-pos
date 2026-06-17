<?php

namespace App\Http\Controllers;

use App\Mail\NotificarProveedor;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        // Recordar el filtro de stock bajo para volver a él tras editar
        if ($request->boolean('stock_bajo')) {
            session(['filtro_stock_bajo' => true]);
        } else {
            session()->forget('filtro_stock_bajo');
        }

        $query = Producto::with(['categoria:id,nombre', 'marca:id,nombre', 'proveedor:id,nombre']);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->boolean('stock_bajo')) {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        $productos = $query->orderBy('nombre')->paginate(15)->withQueryString()
            ->through(fn ($p) => [
                'id'            => $p->id,
                'nombre'        => $p->nombre,
                'categoria'     => $p->categoria->nombre ?? '—',
                'marca'         => $p->marca->nombre ?? '—',
                'proveedor'     => $p->proveedor->nombre ?? null,
                'precio'        => (float) $p->precio,
                'stock'         => (float) $p->stock,
                'stock_minimo'  => (float) $p->stock_minimo,
                'unidad_medida' => $p->unidad_medida,
                'activo'        => (bool) $p->activo,
                'stock_bajo'    => (float) $p->stock <= (float) $p->stock_minimo,
            ]);

        return \Inertia\Inertia::render('Productos/Index', [
            'productos'  => $productos,
            'categorias' => Categoria::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'filtros'    => [
                'buscar'       => $request->buscar ?? '',
                'categoria_id' => $request->categoria_id ?? '',
                'stock_bajo'   => $request->boolean('stock_bajo'),
            ],
        ]);
    }

    public function create()
    {
        return \Inertia\Inertia::render('Productos/Create', $this->opcionesCatalogos());
    }

    public function store(StoreProductoRequest $request)
    {
        Producto::create($request->validated() + ['activo' => true]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        return \Inertia\Inertia::render('Productos/Edit', [
            'producto' => [
                'id'            => $producto->id,
                'nombre'        => $producto->nombre,
                'categoria_id'  => $producto->categoria_id,
                'marca_id'      => $producto->marca_id,
                'proveedor_id'  => $producto->proveedor_id,
                'precio'        => (float) $producto->precio,
                'stock'         => (float) $producto->stock,
                'stock_minimo'  => (float) $producto->stock_minimo,
                'unidad_medida' => $producto->unidad_medida,
            ],
        ] + $this->opcionesCatalogos());
    }

    private function opcionesCatalogos(): array
    {
        return [
            'categorias'  => Categoria::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'marcas'      => Marca::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ];
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $producto->update($request->validated());

        $route = session('filtro_stock_bajo')
            ? route('productos.index', ['stock_bajo' => 1])
            : route('productos.index');

        return redirect($route)->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => !$producto->activo]);

        $mensaje = $producto->activo ? 'Producto activado.' : 'Producto desactivado.';

        return redirect()->route('productos.index')
            ->with('success', $mensaje);
    }

    public function notificarProveedor(Producto $producto)
    {
        $proveedor = $producto->proveedor;

        if (!$proveedor) {
            return redirect()->route('productos.index')
                ->with('error', 'Este producto no tiene un proveedor asignado.');
        }

        Mail::to($proveedor->email)->send(new NotificarProveedor($proveedor, $producto));

        return redirect()->route('productos.index')
            ->with('success', "Notificación enviada a {$proveedor->nombre} ({$proveedor->email}).");
    }
}