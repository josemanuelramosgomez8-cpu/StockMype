<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() === true;
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $esCreacion = $usuario === null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($usuario),
            ],
            'rol' => ['required', Rule::in([
                User::ROL_ADMINISTRADOR,
                User::ROL_DUENO,
                User::ROL_EMPLEADO,
            ])],
            'negocio_id' => [
                Rule::requiredIf(fn (): bool => $this->input('rol') !== User::ROL_ADMINISTRADOR),
                'nullable',
                'integer',
                Rule::exists('negocios', 'id'),
            ],
            'password' => [
                $esCreacion ? 'required' : 'nullable',
                'string',
                Password::defaults(),
            ],
        ];
    }
}
