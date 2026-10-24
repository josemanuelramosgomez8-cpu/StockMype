<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('sku')) {
            $this->merge(['sku' => mb_strtoupper(trim((string) $this->input('sku')))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->esDueno() === true
            && $this->user()->negocio_id !== null;
    }

    public function rules(): array
    {
        $negocioId = $this->user()->negocio_id;
        $producto = $this->route('producto');
        $esEdicion = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'sku' => [
                'required',
                'string',
                'max:80',
                Rule::unique('productos', 'sku')
                    ->where('negocio_id', $negocioId)
                    ->ignore($producto),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'categoria_id' => [
                'nullable',
                Rule::exists('categorias', 'id')->where('negocio_id', $negocioId),
            ],
            'proveedor_id' => [
                'nullable',
                Rule::exists('proveedores', 'id')->where('negocio_id', $negocioId),
            ],
            'precio_compra' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'precio_venta' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'stock_actual' => $esEdicion
                ? ['prohibited']
                : ['required', 'numeric', 'min:0', 'max:999999999', 'decimal:0,3'],
            'stock_minimo' => ['required', 'numeric', 'min:0', 'max:999999999', 'decimal:0,3'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'unidad_medida' => ['required', 'string', 'max:30'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
