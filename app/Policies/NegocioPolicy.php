<?php

namespace App\Policies;

use App\Models\Negocio;
use App\Models\User;

class NegocioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esAdministrador();
    }

    public function view(User $user, Negocio $negocio): bool
    {
        return $user->esAdministrador() || $user->negocio_id === $negocio->id;
    }

    public function create(User $user): bool
    {
        return $user->esAdministrador();
    }

    public function update(User $user, Negocio $negocio): bool
    {
        return $user->esAdministrador()
            || ($user->esDueno() && $user->negocio_id === $negocio->id);
    }

    public function delete(User $user, Negocio $negocio): bool
    {
        return $user->esAdministrador();
    }
}
