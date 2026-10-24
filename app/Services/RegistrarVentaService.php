<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrarVentaService
{
    private const MAX_MONTO_CENTIMOS = 999999999999;

    public function registrar(User $usuario, array $datos): Venta
    {
        return DB::transaction(function () use ($usuario, $datos): Venta {
            $lineas = collect($datos['productos'])
                ->map(fn (array $linea): array => [
                    'producto_id' => (int) $linea['producto_id'],
                    'cantidad_milesimas' => $this->aMilésimas($linea['cantidad']),
                    'descuento_centimos' => $this->aCentimos($linea['descuento'] ?? 0),
                ])
                ->sortBy('producto_id')
                ->values();

            $productos = Producto::query()
                ->where('negocio_id', $usuario->negocio_id)
                ->where('activo', true)
                ->whereNull('deleted_at')
                ->whereIn('id', $lineas->pluck('producto_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($productos->count() !== $lineas->count()) {
                throw ValidationException::withMessages([
                    'productos' => 'Uno o más productos ya no están disponibles para la venta.',
                ]);
            }

            $subtotalCentimos = 0;
            $descuentoCentimos = 0;
            $lineasCalculadas = [];

            foreach ($lineas as $indice => $linea) {
                /** @var Producto $producto */
                $producto = $productos->get($linea['producto_id']);
                $stockDisponible = $this->aMilésimas($producto->stock_actual);

                if ($linea['cantidad_milesimas'] > $stockDisponible) {
                    throw ValidationException::withMessages([
                        'productos' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock_actual} {$producto->unidad_medida}.",
                    ]);
                }

                $precioCentimos = $this->aCentimos($producto->precio_venta);
                if ($precioCentimos > 0
                    && $linea['cantidad_milesimas'] > intdiv(self::MAX_MONTO_CENTIMOS * 1000, $precioCentimos)) {
                    throw ValidationException::withMessages([
                        'productos' => "El importe de {$producto->nombre} supera el máximo permitido por venta.",
                    ]);
                }

                $brutoCentimos = intdiv(
                    ($precioCentimos * $linea['cantidad_milesimas']) + 500,
                    1000
                );
                $descuentoLinea = $linea['descuento_centimos'];

                if ($descuentoLinea > $brutoCentimos) {
                    throw ValidationException::withMessages([
                        "productos.{$indice}.descuento" => "El descuento para {$producto->nombre} no puede superar el importe de esa línea.",
                    ]);
                }

                if ($subtotalCentimos > self::MAX_MONTO_CENTIMOS - $brutoCentimos) {
                    throw ValidationException::withMessages([
                        'productos' => 'El subtotal supera el máximo permitido por venta.',
                    ]);
                }

                $subtotalCentimos += $brutoCentimos;
                $descuentoCentimos += $descuentoLinea;
                $lineasCalculadas[] = [
                    'producto' => $producto,
                    'cantidad_milesimas' => $linea['cantidad_milesimas'],
                    'precio_centimos' => $precioCentimos,
                    'bruto_centimos' => $brutoCentimos,
                    'descuento_centimos' => $descuentoLinea,
                ];
            }

            if ($descuentoCentimos > self::MAX_MONTO_CENTIMOS) {
                throw ValidationException::withMessages([
                    'productos' => 'El descuento supera el máximo permitido por venta.',
                ]);
            }

            $numero = 'V-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(6));

            $venta = Venta::create([
                'negocio_id' => $usuario->negocio_id,
                'user_id' => $usuario->id,
                'numero' => $numero,
                'subtotal' => $this->desdeCentimos($subtotalCentimos),
                'descuento' => $this->desdeCentimos($descuentoCentimos),
                'total' => $this->desdeCentimos($subtotalCentimos - $descuentoCentimos),
                'estado' => 'completada',
                'fecha_venta' => now(),
                'observaciones' => isset($datos['observaciones']) ? trim($datos['observaciones']) : null,
            ]);

            foreach ($lineasCalculadas as $linea) {
                /** @var Producto $producto */
                $producto = $linea['producto'];
                $stockAnterior = $this->aMilésimas($producto->stock_actual);
                $stockResultante = $stockAnterior - $linea['cantidad_milesimas'];
                $producto->stock_actual = $this->desdeMilésimas($stockResultante);
                $producto->save();

                $venta->detalles()->create([
                    'producto_id' => $producto->id,
                    'nombre_producto' => $producto->nombre,
                    'sku' => $producto->sku,
                    'cantidad' => $this->desdeMilésimas($linea['cantidad_milesimas']),
                    'precio_unitario' => $this->desdeCentimos($linea['precio_centimos']),
                    'descuento' => $this->desdeCentimos($linea['descuento_centimos']),
                    'subtotal' => $this->desdeCentimos($linea['bruto_centimos'] - $linea['descuento_centimos']),
                ]);

                $producto->movimientosInventario()->create([
                    'negocio_id' => $usuario->negocio_id,
                    'user_id' => $usuario->id,
                    'tipo' => MovimientoInventario::TIPO_SALIDA,
                    'cantidad' => $this->desdeMilésimas($linea['cantidad_milesimas']),
                    'stock_anterior' => $this->desdeMilésimas($stockAnterior),
                    'stock_resultante' => $this->desdeMilésimas($stockResultante),
                    'motivo' => 'Venta '.$venta->numero,
                ]);
            }

            return $venta->load(['detalles', 'user']);
        });
    }

    private function aMilésimas(string|int|float $cantidad): int
    {
        return (int) round((float) $cantidad * 1000);
    }

    private function aCentimos(string|int|float $cantidad): int
    {
        return (int) round((float) $cantidad * 100);
    }

    private function desdeMilésimas(int $cantidad): string
    {
        return number_format($cantidad / 1000, 3, '.', '');
    }

    private function desdeCentimos(int $cantidad): string
    {
        return number_format($cantidad / 100, 2, '.', '');
    }
}
