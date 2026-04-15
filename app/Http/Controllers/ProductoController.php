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
    $query = Producto::with(['categoria', 'marca']);

    if ($request->filled('buscar')) {
        $query->where('nombre', 'like', '%' . $request->buscar . '%');
    }

    if ($request->filled('categoria_id')) {
        $query->where('categoria_id', $request->categoria_id);
    }

    $productos  = $query->orderBy('nombre')->paginate(15);
    $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

    return view('productos.index', compact('productos', 'categorias'));
}

    public function create()
    {
        $categorias  = Categoria::where('activo', true)->orderBy('nombre')->get();
        $marcas      = Marca::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        return view('productos.create', compact('categorias', 'marcas', 'proveedores'));
    }

    public function store(StoreProductoRequest $request)
    {
        Producto::create($request->validated() + ['activo' => true]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $categorias  = Categoria::where('activo', true)->orderBy('nombre')->get();
        $marcas      = Marca::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        return view('productos.edit', compact('producto', 'categorias', 'marcas', 'proveedores'));
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