<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReporteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('tipo')) {
            $this->merge(['tipo' => 'ventas']);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->negocio_id !== null
            && ($this->user()->esDueno() || $this->user()->esEmpleado());
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['ventas', 'inventario'])],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }
}
