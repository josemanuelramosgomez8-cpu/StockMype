<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
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

        DB::transaction(function () use ($alerta): void {
            if ($alerta->estado !== Alerta::ESTADO_LEIDA) {
                $alerta->update([
                    'estado' => Alerta::ESTADO_LEIDA,
                    'leida_at' => now(),
                ]);
            }

            // La alerta pertenece al negocio, no a una sola persona.
            // Por eso se marcan como leídas las notificaciones de TODOS
            // los usuarios del negocio (dueño y empleados), no solo las
            // de quien presionó el botón. Así la campanita de cada uno
            // queda al día y coincide con el dashboard.
            $usuariosDelNegocio = User::where('negocio_id', $alerta->negocio_id)->pluck('id');

            DatabaseNotification::query()
                ->where('notifiable_type', (new User)->getMorphClass())
                ->whereIn('notifiable_id', $usuariosDelNegocio)
                ->whereNull('read_at')
                ->where('data->alerta_id', $alerta->id)
                ->update(['read_at' => now()]);
        });

        return redirect()->route('alertas.index')->with('status', 'Alerta marcada como leída.');
    }
}