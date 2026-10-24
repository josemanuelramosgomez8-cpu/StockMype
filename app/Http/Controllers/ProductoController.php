<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Producto::class);

        $productos = $request->user()->negocio->productos()
            ->with(['categoria', 'proveedor'])
            ->when($request->filled('buscar'), function ($query) use ($request): void {
                $buscar = trim((string) $request->input('buscar'));
                $query->where(function ($consulta) use ($buscar): void {
                    $consulta->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('sku', 'like', "%{$buscar}%");
                });
            })
            ->when($request->input('estado') === 'inactivos', fn ($query) => $query->where('activo', false))
            ->when($request->input('estado') === 'activos', fn ($query) => $query->where('activo', true))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('productos.index', compact('productos'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', new Producto(['negocio_id' => $request->user()->negocio_id]));

        return view('productos.form', [
            'producto' => new Producto(['activo' => true, 'unidad_medida' => 'unidad']),
            ...$this->opcionesFormulario($request),
        ]);
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        $this->authorize('create', new Producto(['negocio_id' => $request->user()->negocio_id]));

        $datos = $request->validated();
        $datos['negocio_id'] = $request->user()->negocio_id;
        $datos['activo'] = $request->boolean('activo');

        DB::transaction(function () use ($datos, $request): void {
            $producto = Producto::create($datos);

            if ((float) $producto->stock_actual > 0) {
                $producto->movimientosInventario()->create([
                    'negocio_id' => $producto->negocio_id,
                    'user_id' => $request->user()->id,
                    'tipo' => 'entrada',
                    'cantidad' => $producto->stock_actual,
                    'stock_anterior' => 0,
                    'stock_resultante' => $producto->stock_actual,
                    'motivo' => 'Stock inicial al crear el producto',
                ]);
            }
        });

        return redirect()->route('productos.index')->with('status', 'Producto creado correctamente.');
    }

    public function show(Request $request, int $producto): View
    {
        $producto = $request->user()->negocio->productos()
            ->with(['categoria', 'proveedor'])
            ->findOrFail($producto);
        $this->authorize('view', $producto);

        return view('productos.show', compact('producto'));
    }

    public function edit(Request $request, int $producto): View
    {
        $producto = $request->user()->negocio->productos()->findOrFail($producto);
        $this->authorize('update', $producto);

        return view('productos.form', [
            'producto' => $producto,
            ...$this->opcionesFormulario($request),
        ]);
    }

    public function update(ProductoRequest $request, int $producto): RedirectResponse
    {
        $producto = $request->user()->negocio->productos()->findOrFail($producto);
        $this->authorize('update', $producto);

        $datos = $request->validated();
        unset($datos['stock_actual']);
        $datos['activo'] = $request->boolean('activo');
        $producto->update($datos);

        return redirect()->route('productos.index')->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Request $request, int $producto): RedirectResponse
    {
        $producto = $request->user()->negocio->productos()->findOrFail($producto);
        $this->authorize('delete', $producto);
        $producto->delete();

        return redirect()->route('productos.index')->with('status', 'Producto eliminado correctamente.');
    }

    /**
     * Obtiene categorías y proveedores pertenecientes al negocio autenticado.
     *
     * @return array{categorias: Collection<int, Categoria>, proveedores: Collection<int, Proveedor>}
     */
    private function opcionesFormulario(Request $request): array
    {
        return [
            'categorias' => $request->user()->negocio->categorias()->orderBy('nombre')->get(),
            'proveedores' => $request->user()->negocio->proveedores()->orderBy('nombre')->get(),
        ];
    }
}
