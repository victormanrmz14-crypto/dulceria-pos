<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index()
    {
        return Inertia::render('Catalogos/Proveedores', [
            'proveedores' => Proveedor::withCount('productos')->orderBy('nombre')->get()
                ->map(fn ($p) => [
                    'id'              => $p->id,
                    'nombre'          => $p->nombre,
                    'email'           => $p->email,
                    'telefono'        => $p->telefono,
                    'notas'           => $p->notas,
                    'activo'          => (bool) $p->activo,
                    'productos_count' => $p->productos_count,
                ])->values(),
        ]);
    }

    // El alta/edición se hace con modales en el index
    public function create()
    {
        return redirect()->route('catalogos.proveedores.index');
    }

    public function edit(Proveedor $proveedor)
    {
        return redirect()->route('catalogos.proveedores.index');
    }

    public function store(StoreProveedorRequest $request)
    {
        Proveedor::create($request->validated() + ['activo' => true]);

        return redirect()->route('catalogos.proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor)
    {
        $proveedor->update($request->validated());

        return redirect()->route('catalogos.proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => !$proveedor->activo]);

        $mensaje = $proveedor->activo ? 'Proveedor activado.' : 'Proveedor desactivado.';

        return redirect()->route('catalogos.proveedores.index')
            ->with('success', $mensaje);
    }
}
