<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegistroNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'negocio_nombre' => ['required', 'string', 'max:255'],
            'rubro' => ['required', 'in:gastronomia,retail'],
            'ruc' => ['nullable', 'digits:11', 'unique:negocios,ruc'],
        ];
    }
}
