<?php

namespace App\Policies;

use App\Models\MovimientoInventario;
use App\Models\User;

class MovimientoInventarioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esDueno() && $user->negocio_id !== null;
    }

    public function view(User $user, MovimientoInventario $movimiento): bool
    {
        return $user->esDueno() && $user->negocio_id === $movimiento->negocio_id;
    }

    public function create(User $user): bool
    {
        return $user->esDueno() && $user->negocio_id !== null;
    }
}
