<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NegocioRequest;
use App\Models\Negocio;
use App\Services\RegistrarAuditoriaAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NegocioController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Negocio::class);

        $negocios = Negocio::query()
            ->withCount(['users', 'productos'])
            ->when($request->filled('buscar'), function ($query) use ($request): void {
                $termino = trim((string) $request->input('buscar'));
                $query->where(function ($consulta) use ($termino): void {
                    $consulta->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('ruc', 'like', "%{$termino}%");
                });
            })
            ->when($request->filled('rubro'), fn ($query) => $query->where('rubro', $request->input('rubro')))
            ->when($request->input('estado') === 'activo', fn ($query) => $query->where('activo', true))
            ->when($request->input('estado') === 'suspendido', fn ($query) => $query->where('activo', false))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('admin.negocios.index', compact('negocios'));
    }

    public function create(): View
    {
        $this->authorize('create', Negocio::class);

        return view('admin.negocios.form', ['negocio' => new Negocio]);
    }

    public function store(NegocioRequest $request, RegistrarAuditoriaAdmin $auditoria): RedirectResponse
    {
        $this->authorize('create', Negocio::class);
        $negocio = Negocio::create($request->validated());
        $auditoria->registrar($request, 'negocio.creado', "Creó el negocio {$negocio->nombre}.", $negocio);

        return redirect()->route('admin.negocios.index')->with('status', 'Negocio creado correctamente.');
    }

    public function edit(Negocio $negocio): View
    {
        $this->authorize('update', $negocio);

        return view('admin.negocios.form', compact('negocio'));
    }

    public function update(NegocioRequest $request, Negocio $negocio, RegistrarAuditoriaAdmin $auditoria): RedirectResponse
    {
        $this->authorize('update', $negocio);
        $datos = $request->validated();
        $negocio->update($datos);
        $auditoria->registrar(
            $request,
            'negocio.actualizado',
            "Actualizó los datos del negocio {$negocio->nombre}.",
            $negocio,
            ['campos' => array_keys($datos)]
        );

        return redirect()->route('admin.negocios.index')->with('status', 'Negocio actualizado correctamente.');
    }

    public function cambiarEstado(Request $request, Negocio $negocio, RegistrarAuditoriaAdmin $auditoria): RedirectResponse
    {
        $this->authorize('update', $negocio);
        $datos = $request->validate(['activo' => ['required', 'boolean']]);
        $estadoAnterior = $negocio->activo;
        $negocio->update(['activo' => $datos['activo']]);

        $auditoria->registrar(
            $request,
            $negocio->activo ? 'negocio.activado' : 'negocio.suspendido',
            ($negocio->activo ? 'Activó' : 'Suspendió')." el negocio {$negocio->nombre}.",
            $negocio,
            ['activo_anterior' => $estadoAnterior, 'activo_nuevo' => $negocio->activo]
        );

        return redirect()->route('admin.negocios.index')->with(
            'status',
            $negocio->activo ? 'Negocio activado correctamente.' : 'Negocio suspendido correctamente.'
        );
    }

    public function destroy(Request $request, Negocio $negocio, RegistrarAuditoriaAdmin $auditoria): RedirectResponse
    {
        $this->authorize('delete', $negocio);

        $tieneDatos = $negocio->users()->exists()
            || $negocio->categorias()->exists()
            || $negocio->proveedores()->exists()
            || $negocio->productos()->withTrashed()->exists()
            || $negocio->movimientosInventario()->exists()
            || $negocio->ventas()->exists()
            || $negocio->alertas()->exists();

        if ($tieneDatos) {
            return back()->withErrors([
                'negocio' => 'No se puede eliminar un negocio que tiene usuarios o información registrada.',
            ]);
        }

        $nombre = $negocio->nombre;
        $auditoria->registrar($request, 'negocio.eliminado', "Eliminó el negocio {$nombre}.", $negocio);
        $negocio->delete();

        return redirect()->route('admin.negocios.index')->with('status', 'Negocio eliminado correctamente.');
    }
}
