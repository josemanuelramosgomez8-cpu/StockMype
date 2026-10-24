<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class VentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->negocio_id !== null
            && ($this->user()->esDueno() || $this->user()->esEmpleado());
    }

    public function rules(): array
    {
        return [
            'productos' => ['required', 'array', 'min:1', 'max:100'],
            'productos.*.producto_id' => [
                'required',
                'integer',
                Rule::exists('productos', 'id')
                    ->where('negocio_id', $this->user()->negocio_id)
                    ->where('activo', true)
                    ->whereNull('deleted_at'),
            ],
            'productos.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:999999999'],
            'productos.*.descuento' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:999999999'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $productos = $this->input('productos');

                if (! is_array($productos)) {
                    return;
                }

                $ids = array_column($productos, 'producto_id');

                if (count($ids) !== count(array_unique($ids))) {
                    $validator->errors()->add('productos', 'No repitas productos en la venta; agrega la cantidad en una sola línea.');
                }
            },
        ];
    }
}
