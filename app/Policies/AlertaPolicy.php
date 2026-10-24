<?php

namespace App\Policies;

use App\Models\Alerta;
use App\Models\User;

class AlertaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->negocio_id !== null
            && ($user->esDueno() || $user->esEmpleado());
    }

    public function update(User $user, Alerta $alerta): bool
    {
        return ($user->esDueno() || $user->esEmpleado())
            && $user->negocio_id === $alerta->negocio_id;
    }
}
