<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::orderBy('nombre')->get();
        return view('catalogos.marcas.index', compact('marcas'));
    }

    public function create()
    {
        return view('catalogos.marcas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:marcas,nombre'],
        ]);

        Marca::create(['nombre' => $request->nombre, 'activo' => true]);

        return redirect()->route('catalogos.marcas.index')
            ->with('success', 'Marca creada correctamente.');
    }

    public function edit(Marca $marca)
    {
        return view('catalogos.marcas.edit', compact('marca'));
    }

    public function update(Request $request, Marca $marca)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:marcas,nombre,' . $marca->id],
        ]);

        $marca->update(['nombre' => $request->nombre]);

        return redirect()->route('catalogos.marcas.index')
            ->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Marca $marca)
    {
        $marca->update(['activo' => !$marca->activo]);

        $mensaje = $marca->activo ? 'Marca activada.' : 'Marca desactivada.';

        return redirect()->route('catalogos.marcas.index')
            ->with('success', $mensaje);
    }
}