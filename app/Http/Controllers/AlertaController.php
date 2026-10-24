<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Alerta::class);

        $alertas = $request->user()->negocio->alertas()
            ->with('producto')
            ->latest()
            ->paginate(20);

        return view('alertas.index', compact('alertas'));
    }

    public function marcarLeida(Request $request, Alerta $alerta): RedirectResponse
    {
        $alerta = $request->user()->negocio->alertas()->findOrFail($alerta->id);
        $this->authorize('update', $alerta);

        DB::transaction(function () use ($request, $alerta): void {
            if ($alerta->estado !== Alerta::ESTADO_LEIDA) {
                $alerta->update([
                    'estado' => Alerta::ESTADO_LEIDA,
                    'leida_at' => now(),
                ]);
            }

            $request->user()->notifications()
                ->whereNull('read_at')
                ->where('data->alerta_id', $alerta->id)
                ->update(['read_at' => now()]);
        });

        return redirect()->route('alertas.index')->with('status', 'Alerta marcada como leída.');
    }
}
