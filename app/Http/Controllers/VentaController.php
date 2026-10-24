<?php

namespace App\Http\Controllers;

use App\Http\Requests\VentaRequest;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\RegistrarVentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Venta::class);

        $ventas = $request->user()->negocio->ventas()
            ->with('user')
            ->latest('fecha_venta')
            ->paginate(20);

        return view('ventas.index', compact('ventas'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', new Venta(['negocio_id' => $request->user()->negocio_id]));

        $productos = $request->user()->negocio->productos()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'sku', 'nombre', 'precio_venta', 'stock_actual', 'unidad_medida']);
        $productosParaFormulario = $productos->map(fn (Producto $producto): array => [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'sku' => $producto->sku,
            'precio' => $producto->precio_venta,
            'stock' => $producto->stock_actual,
            'unidad' => $producto->unidad_medida,
        ])->values()->all();

        return view('ventas.create', compact('productos', 'productosParaFormulario'));
    }

    public function store(VentaRequest $request, RegistrarVentaService $servicio): RedirectResponse
    {
        $venta = $servicio->registrar($request->user(), $request->validated());

        return redirect()->route('ventas.show', $venta)
            ->with('status', 'Venta registrada; el stock se actualizó correctamente.');
    }

    public function show(Request $request, int $venta): View
    {
        $venta = $request->user()->negocio->ventas()
            ->with(['detalles', 'user'])
            ->findOrFail($venta);
        $this->authorize('view', $venta);

        return view('ventas.show', compact('venta'));
    }
}
