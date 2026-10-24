<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovimientoInventarioRequest;
use App\Models\MovimientoInventario;
use App\Services\MovimientoInventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MovimientoInventario::class);

        $movimientos = $request->user()->negocio->movimientosInventario()
            ->with(['producto', 'user'])
            ->latest()
            ->paginate(20);

        return view('movimientos.index', compact('movimientos'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', MovimientoInventario::class);

        $productos = $request->user()->negocio->productos()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('movimientos.create', [
            'productos' => $productos,
            'productoSeleccionado' => $request->integer('producto_id'),
        ]);
    }

    public function store(
        MovimientoInventarioRequest $request,
        MovimientoInventarioService $servicio
    ): RedirectResponse {
        $this->authorize('create', MovimientoInventario::class);
        $movimiento = $servicio->registrar($request->user(), $request->validated());

        return redirect()->route('movimientos.show', $movimiento)
            ->with('status', 'Movimiento registrado y stock actualizado.');
    }

    public function show(Request $request, int $movimiento): View
    {
        $movimiento = $request->user()->negocio->movimientosInventario()
            ->with(['producto', 'user'])
            ->findOrFail($movimiento);
        $this->authorize('view', $movimiento);

        return view('movimientos.show', compact('movimiento'));
    }
}
