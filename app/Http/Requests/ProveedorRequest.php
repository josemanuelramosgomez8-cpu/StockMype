<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esDueno() === true
            && $this->user()->negocio_id !== null;
    }

    public function rules(): array
    {
        $negocioId = $this->user()->negocio_id;
        $proveedor = $this->route('proveedor');

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'ruc' => [
                'nullable',
                'digits:11',
                Rule::unique('proveedores', 'ruc')
                    ->where('negocio_id', $negocioId)
                    ->ignore($proveedor)
                    ->whereNotNull('ruc'),
            ],
            'contacto' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
