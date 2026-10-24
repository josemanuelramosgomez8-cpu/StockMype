<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;

class VentaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->negocio_id !== null
            && ($user->esDueno() || $user->esEmpleado());
    }

    public function view(User $user, Venta $venta): bool
    {
        return ($user->esDueno() || $user->esEmpleado())
            && $user->negocio_id === $venta->negocio_id;
    }

    public function create(User $user): bool
    {
        return $user->negocio_id !== null
            && ($user->esDueno() || $user->esEmpleado());
    }
}
