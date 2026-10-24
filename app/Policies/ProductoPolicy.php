<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProductoPolicy extends DatosNegocioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->negocio_id !== null
            && ($user->esDueno() || $user->esEmpleado());
    }

    public function view(User $user, Model $modelo): bool
    {
        return $modelo instanceof Producto
            && ($user->esDueno() || $user->esEmpleado())
            && $this->perteneceAlNegocio($user, $modelo);
    }
}
