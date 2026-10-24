<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Proveedor::class);

        $proveedores = $request->user()->negocio->proveedores()
            ->withCount([
                'productos as productos_count' => fn (Builder $query) => $query->withTrashed(),
            ])
            ->orderBy('nombre')
            ->paginate(15);

        return view('proveedores.index', compact('proveedores'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', new Proveedor(['negocio_id' => $request->user()->negocio_id]));

        return view('proveedores.form', ['proveedor' => new Proveedor]);
    }

    public function store(ProveedorRequest $request): RedirectResponse
    {
        $proveedor = new Proveedor(['negocio_id' => $request->user()->negocio_id]);
        $this->authorize('create', $proveedor);
        $proveedor->fill($request->validated())->save();

        return redirect()->route('proveedores.index')->with('status', 'Proveedor creado correctamente.');
    }

    public function edit(Request $request, int $proveedor): View
    {
        $proveedor = $request->user()->negocio->proveedores()->findOrFail($proveedor);
        $this->authorize('update', $proveedor);

        return view('proveedores.form', compact('proveedor'));
    }

    public function update(ProveedorRequest $request, int $proveedor): RedirectResponse
    {
        $proveedor = $request->user()->negocio->proveedores()->findOrFail($proveedor);
        $this->authorize('update', $proveedor);
        $proveedor->update($request->validated());

        return redirect()->route('proveedores.index')->with('status', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Request $request, int $proveedor): RedirectResponse
    {
        $proveedor = $request->user()->negocio->proveedores()->findOrFail($proveedor);
        $this->authorize('delete', $proveedor);

        if ($proveedor->productos()->withTrashed()->exists()) {
            return back()->withErrors([
                'proveedor' => 'No puedes eliminar este proveedor porque tiene productos asociados.',
            ]);
        }

        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('status', 'Proveedor eliminado correctamente.');
    }
}
