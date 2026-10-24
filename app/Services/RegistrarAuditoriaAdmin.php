<?php

namespace App\Services;

use App\Models\RegistroAuditoriaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RegistrarAuditoriaAdmin
{
    public function registrar(
        Request $request,
        string $evento,
        string $descripcion,
        ?Model $entidad = null,
        array $detalles = []
    ): RegistroAuditoriaAdmin {
        $actor = $request->user();

        return RegistroAuditoriaAdmin::create([
            'actor_id' => $actor?->id,
            'actor_nombre' => $actor?->name ?? 'Sistema',
            'evento' => $evento,
            'entidad' => $entidad ? $entidad::class : null,
            'entidad_id' => $entidad?->getKey(),
            'descripcion' => $descripcion,
            'detalles' => $detalles ?: null,
            'ip' => $request->ip(),
        ]);
    }
}
