<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    // Listar todas las categorías
    public function index()
    {
        return Inertia::render('Catalogos/Simple', [
            'titulo'   => 'Categorías',
            'singular' => 'categoría',
            'icono'    => '🏷️',
            'base'     => '/catalogos/categorias',
            'items'    => Categoria::orderBy('nombre')->get(['id', 'nombre', 'activo'])
                ->map(fn ($c) => [
                    'id'     => $c->id,
                    'nombre' => $c->nombre,
                    'activo' => (bool) $c->activo,
                ])->values(),
        ]);
    }

    // El alta/edición se hace con modales en el index
    public function create()
    {
        return redirect()->route('catalogos.categorias.index');
    }

    public function edit(Categoria $categoria)
    {
        return redirect()->route('catalogos.categorias.index');
    }

    // Guardar nueva categoría
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150',
                Rule::unique('categorias')->where('tenant_id', auth()->user()->tenant_id)],
        ]);

        Categoria::create([
            'nombre' => $request->nombre,
            'activo' => true,
        ]);

        return redirect()->route('catalogos.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    // Actualizar categoría
    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150',
                Rule::unique('categorias')->where('tenant_id', auth()->user()->tenant_id)->ignore($categoria->id)],
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
