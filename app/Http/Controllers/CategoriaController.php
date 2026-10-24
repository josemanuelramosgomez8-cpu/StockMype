<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Categoria::class);

        $categorias = $request->user()->negocio->categorias()
            ->withCount([
                'productos as productos_count' => fn (Builder $query) => $query->withTrashed(),
            ])
            ->orderBy('nombre')
            ->paginate(15);

        return view('categorias.index', compact('categorias'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', new Categoria(['negocio_id' => $request->user()->negocio_id]));

        return view('categorias.form', ['categoria' => new Categoria]);
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        $categoria = new Categoria(['negocio_id' => $request->user()->negocio_id]);
        $this->authorize('create', $categoria);
        $categoria->fill($request->validated())->save();

        return redirect()->route('categorias.index')->with('status', 'Categoría creada correctamente.');
    }

    public function edit(Request $request, int $categoria): View
    {
        $categoria = $request->user()->negocio->categorias()->findOrFail($categoria);
        $this->authorize('update', $categoria);

        return view('categorias.form', compact('categoria'));
    }

    public function update(CategoriaRequest $request, int $categoria): RedirectResponse
    {
        $categoria = $request->user()->negocio->categorias()->findOrFail($categoria);
        $this->authorize('update', $categoria);
        $categoria->update($request->validated());

        return redirect()->route('categorias.index')->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(Request $request, int $categoria): RedirectResponse
    {
        $categoria = $request->user()->negocio->categorias()->findOrFail($categoria);
        $this->authorize('delete', $categoria);

        if ($categoria->productos()->withTrashed()->exists()) {
            return back()->withErrors([
                'categoria' => 'No puedes eliminar esta categoría porque tiene productos asociados.',
            ]);
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('status', 'Categoría eliminada correctamente.');
    }
}
