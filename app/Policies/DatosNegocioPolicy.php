<?php

namespace App\Policies;

use App\Models\DetalleVenta;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Model;

class DatosNegocioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esDueno() && $user->negocio_id !== null;
    }

    public function view(User $user, Model $modelo): bool
    {
        return $user->esDueno() && $this->perteneceAlNegocio($user, $modelo);
    }

    public function create(User $user, Model $modelo): bool
    {
        if (! $this->perteneceAlNegocio($user, $modelo)) {
            return false;
        }

        return $user->esDueno()
            || ($modelo instanceof Venta && $user->esEmpleado());
    }

    public function update(User $user, Model $modelo): bool
    {
        return $user->esDueno() && $this->perteneceAlNegocio($user, $modelo);
    }

    public function delete(User $user, Model $modelo): bool
    {
        return $user->esDueno() && $this->perteneceAlNegocio($user, $modelo);
    }

    protected function perteneceAlNegocio(User $user, Model $modelo): bool
    {
        $negocioId = $modelo instanceof DetalleVenta
            ? $modelo->venta()->value('negocio_id')
            : $modelo->getAttribute('negocio_id');

        return ! $user->esAdministrador()
            && $user->negocio_id !== null
            && (int) $negocioId === $user->negocio_id;
    }
}
