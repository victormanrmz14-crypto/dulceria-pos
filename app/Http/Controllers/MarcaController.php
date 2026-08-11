<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MarcaController extends Controller
{
    public function index()
    {
        return Inertia::render('Catalogos/Simple', [
            'titulo'   => 'Marcas',
            'singular' => 'marca',
            'icono'    => '🏭',
            'base'     => '/catalogos/marcas',
            'items'    => Marca::orderBy('nombre')->get(['id', 'nombre', 'activo'])
                ->map(fn ($m) => [
                    'id'     => $m->id,
                    'nombre' => $m->nombre,
                    'activo' => (bool) $m->activo,
                ])->values(),
        ]);
    }

    // El alta/edición se hace con modales en el index
    public function create()
    {
        return redirect()->route('catalogos.marcas.index');
    }

    public function edit(Marca $marca)
    {
        return redirect()->route('catalogos.marcas.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150',
                Rule::unique('marcas')->where('tenant_id', auth()->user()->tenant_id)],
        ]);

        Marca::create(['nombre' => $request->nombre, 'activo' => true]);

        return redirect()->route('catalogos.marcas.index')
            ->with('success', 'Marca creada correctamente.');
    }

    public function update(Request $request, Marca $marca)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150',
                Rule::unique('marcas')->where('tenant_id', auth()->user()->tenant_id)->ignore($marca->id)],
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
