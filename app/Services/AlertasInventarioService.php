<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Producto;
use App\Notifications\AlertaInventarioNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AlertasInventarioService
{
    public const DIAS_POR_VENCER = 7;

    public function revisar(Producto $producto): void
    {
        DB::transaction(function () use ($producto): void {
            $producto = Producto::query()
                ->withTrashed()
                ->lockForUpdate()
                ->find($producto->id);

            if (! $producto || $producto->trashed()) {
                return;
            }

            $tiposActivos = $this->tiposActivos($producto);
            $alertasActivas = Alerta::query()
                ->where('producto_id', $producto->id)
                ->whereNull('resuelta_at')
                ->get()
                ->keyBy('tipo');

            foreach ($alertasActivas as $tipo => $alerta) {
                if (! in_array($tipo, $tiposActivos, true)) {
                    $alerta->update([
                        'estado' => Alerta::ESTADO_LEIDA,
                        'leida_at' => $alerta->leida_at ?? now(),
                        'resuelta_at' => now(),
                    ]);
                    $this->marcarNotificacionesLeidas($alerta);
                }
            }

            foreach ($tiposActivos as $tipo) {
                if ($alertasActivas->has($tipo)) {
                    continue;
                }

                $alerta = Alerta::create([
                    'negocio_id' => $producto->negocio_id,
                    'producto_id' => $producto->id,
                    'tipo' => $tipo,
                    'estado' => Alerta::ESTADO_PENDIENTE,
                    'mensaje' => $this->mensaje($producto, $tipo),
                ]);
                $alerta->load('producto');

                DB::afterCommit(fn () => $this->notificarNegocio($alerta));
            }
        });
    }

    private function tiposActivos(Producto $producto): array
    {
        if (! $producto->activo || ! $producto->negocio?->activo) {
            return [];
        }

        $tipos = [];
        $stock = (float) $producto->stock_actual;
        $minimo = (float) $producto->stock_minimo;

        if ($stock <= 0) {
            $tipos[] = Alerta::TIPO_AGOTADO;
        } elseif ($stock <= $minimo) {
            $tipos[] = Alerta::TIPO_STOCK_BAJO;
        }

        if ($producto->fecha_vencimiento
            && $producto->fecha_vencimiento->isAfter(today()->subDay())
            && $producto->fecha_vencimiento->lte(today()->addDays(self::DIAS_POR_VENCER))) {
            $tipos[] = Alerta::TIPO_POR_VENCER;
        }

        return $tipos;
    }

    private function mensaje(Producto $producto, string $tipo): string
    {
        return match ($tipo) {
            Alerta::TIPO_AGOTADO => "El producto {$producto->nombre} está agotado.",
            Alerta::TIPO_STOCK_BAJO => "El producto {$producto->nombre} tiene {$producto->stock_actual} {$producto->unidad_medida}; su mínimo es {$producto->stock_minimo}.",
            Alerta::TIPO_POR_VENCER => "El producto {$producto->nombre} vence el {$producto->fecha_vencimiento->format('d/m/Y')}.",
        };
    }

    private function notificarNegocio(Alerta $alerta): void
    {
        $usuarios = $alerta->negocio->users()
            ->where('activo', true)
            ->whereIn('rol', ['dueno', 'empleado'])
            ->get();

        Notification::send($usuarios, new AlertaInventarioNotification($alerta));
    }

    private function marcarNotificacionesLeidas(Alerta $alerta): void
    {
        $usuarios = $alerta->negocio->users()->get();
        $fechaLectura = now();

        foreach ($usuarios as $usuario) {
            $usuario->notifications()
                ->whereNull('read_at')
                ->where('data->alerta_id', $alerta->id)
                ->update(['read_at' => $fechaLectura]);
        }
    }
}
