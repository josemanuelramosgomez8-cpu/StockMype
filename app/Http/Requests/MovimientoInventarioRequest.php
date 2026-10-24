<?php

namespace App\Http\Requests;

use App\Models\MovimientoInventario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MovimientoInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esDueno() === true
            && $this->user()->negocio_id !== null;
    }

    public function rules(): array
    {
        return [
            'producto_id' => [
                'required',
                'integer',
                Rule::exists('productos', 'id')
                    ->where('negocio_id', $this->user()->negocio_id)
                    ->where('activo', true)
                    ->whereNull('deleted_at'),
            ],
            'tipo' => ['required', Rule::in([
                MovimientoInventario::TIPO_ENTRADA,
                MovimientoInventario::TIPO_SALIDA,
                MovimientoInventario::TIPO_AJUSTE,
            ])],
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:999999999'],
            'motivo' => ['required', 'string', 'max:255'],
        ];
    }
}
