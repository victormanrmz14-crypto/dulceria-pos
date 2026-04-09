<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // Listar todas las categorías
    public function index()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        return view('catalogos.categorias.index', compact('categorias'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        return view('catalogos.categorias.create');
    }

    // Guardar nueva categoría
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:categorias,nombre'],
        ]);

        Categoria::create([
            'nombre' => $request->nombre,
            'activo' => true,
        ]);

        return redirect()->route('catalogos.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    // Mostrar formulario de edición
    public function edit(Categoria $categoria)
    {
        return view('catalogos.categorias.edit', compact('categoria'));
    }

    // Actualizar categoría
    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:categorias,nombre,' . $categoria->id],
        ]);

        $categoria->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('catalogos.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    // Activar o desactivar categoría
    public function destroy(Categoria $categoria)
    {
        $categoria->update([
            'activo' => !$categoria->activo,
        ]);

        $mensaje = $categoria->activo ? 'Categoría activada.' : 'Categoría desactivada.';

        return redirect()->route('catalogos.categorias.index')
            ->with('success', $mensaje);
    }
}