<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esDueno() === true
            && $this->user()->negocio_id !== null;
    }

    public function rules(): array
    {
        $negocioId = $this->user()->negocio_id;
        $categoria = $this->route('categoria');

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias', 'nombre')
                    ->where('negocio_id', $negocioId)
                    ->ignore($categoria),
            ],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
