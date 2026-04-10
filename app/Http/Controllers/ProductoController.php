<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use Illuminate\Http\Request;

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
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $marcas     = Marca::where('activo', true)->orderBy('nombre')->get();
        return view('productos.create', compact('categorias', 'marcas'));
    }

    public function store(StoreProductoRequest $request)
    {
        Producto::create($request->validated() + ['activo' => true]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $marcas     = Marca::where('activo', true)->orderBy('nombre')->get();
        return view('productos.edit', compact('producto', 'categorias', 'marcas'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $producto->update($request->validated());

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => !$producto->activo]);

        $mensaje = $producto->activo ? 'Producto activado.' : 'Producto desactivado.';

        return redirect()->route('productos.index')
            ->with('success', $mensaje);
    }
}