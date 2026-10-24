<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovimientoInventarioService
{
    public function registrar(User $usuario, array $datos): MovimientoInventario
    {
        return DB::transaction(function () use ($usuario, $datos): MovimientoInventario {
            $producto = Producto::query()
                ->where('negocio_id', $usuario->negocio_id)
                ->where('activo', true)
                ->lockForUpdate()
                ->findOrFail($datos['producto_id']);

            $stockAnterior = $this->aMilésimas($producto->stock_actual);
            $cantidad = $this->aMilésimas($datos['cantidad']);

            $stockResultante = match ($datos['tipo']) {
                MovimientoInventario::TIPO_ENTRADA => $stockAnterior + $cantidad,
                MovimientoInventario::TIPO_SALIDA => $stockAnterior - $cantidad,
                MovimientoInventario::TIPO_AJUSTE => $cantidad,
            };

            if ($stockResultante < 0 || $stockResultante > 999999999999) {
                throw ValidationException::withMessages([
                    'cantidad' => $stockResultante < 0
                        ? 'La salida supera el stock disponible del producto.'
                        : 'El stock resultante supera el límite permitido.',
                ]);
            }

            $cantidadRegistrada = $datos['tipo'] === MovimientoInventario::TIPO_AJUSTE
                ? abs($stockResultante - $stockAnterior)
                : $cantidad;

            if ($datos['tipo'] === MovimientoInventario::TIPO_AJUSTE && $cantidadRegistrada === 0) {
                throw ValidationException::withMessages([
                    'cantidad' => 'El ajuste debe cambiar el stock; ingresa la cantidad final contada.',
                ]);
            }

            $producto->stock_actual = $this->desdeMilésimas($stockResultante);
            $producto->save();

            return $producto->movimientosInventario()->create([
                'negocio_id' => $usuario->negocio_id,
                'user_id' => $usuario->id,
                'tipo' => $datos['tipo'],
                'cantidad' => $this->desdeMilésimas($cantidadRegistrada),
                'stock_anterior' => $this->desdeMilésimas($stockAnterior),
                'stock_resultante' => $this->desdeMilésimas($stockResultante),
                'motivo' => trim($datos['motivo']),
            ]);
        });
    }

    private function aMilésimas(string|int|float $cantidad): int
    {
        return (int) round((float) $cantidad * 1000);
    }

    private function desdeMilésimas(int $cantidad): string
    {
        return number_format($cantidad / 1000, 3, '.', '');
    }
}
