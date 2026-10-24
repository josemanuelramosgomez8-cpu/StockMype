<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistroAuditoriaAdmin;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $registros = RegistroAuditoriaAdmin::query()
            ->with('actor:id,name')
            ->when($request->filled('buscar'), function ($query) use ($request): void {
                $termino = trim((string) $request->input('buscar'));
                $query->where(function ($consulta) use ($termino): void {
                    $consulta->where('descripcion', 'like', "%{$termino}%")
                        ->orWhere('actor_nombre', 'like', "%{$termino}%");
                });
            })
            ->when($request->filled('evento'), fn ($query) => $query->where('evento', $request->input('evento')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $eventos = RegistroAuditoriaAdmin::query()
            ->select('evento')
            ->distinct()
            ->orderBy('evento')
            ->pluck('evento');

        return view('admin.auditoria.index', compact('registros', 'eventos'));
    }
}
