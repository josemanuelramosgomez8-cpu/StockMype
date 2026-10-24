<?php

namespace App\Policies;

use App\Models\User;

class UsuarioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esAdministrador() || $user->esDueno();
    }

    public function view(User $user, User $usuario): bool
    {
        return $user->esAdministrador()
            || ($user->esDueno() && $usuario->negocio_id === $user->negocio_id);
    }

    public function create(User $user): bool
    {
        return $user->esAdministrador() || $user->esDueno();
    }

    public function update(User $user, User $usuario): bool
    {
        if ($user->esAdministrador()) {
            return true;
        }

        return $user->esDueno()
            && $usuario->negocio_id === $user->negocio_id
            && $usuario->rol === User::ROL_EMPLEADO;
    }

    public function delete(User $user, User $usuario): bool
    {
        return $user->esAdministrador()
            || ($user->esDueno()
                && $usuario->negocio_id === $user->negocio_id
                && $usuario->rol === User::ROL_EMPLEADO
                && $user->isNot($usuario));
    }
}
