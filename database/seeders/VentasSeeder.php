<?php

namespace Database\Seeders;

use App\Models\Alerta;
use App\Models\MovimientoInventario;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Services\MovimientoInventarioService;
use App\Services\RegistrarVentaService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VentasSeeder extends Seeder
{
    private const PREFIJOS = [
        'cevicheria' => 'CEV',
        'bodega' => 'BOD',
        'ropa' => 'ROP',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $servicioVentas = app(RegistrarVentaService::class);
            $servicioMovimientos = app(MovimientoInventarioService::class);

            foreach (NegociosSeeder::negocios() as $clave => $datosNegocio) {
                $negocio = Negocio::query()->where('nombre', $datosNegocio['nombre'])->firstOrFail();
                $prefijo = self::PREFIJOS[$clave];
                $productos = Producto::query()
                    ->where('negocio_id', $negocio->id)
                    ->where('sku', 'like', $prefijo.'-%')
                    ->orderBy('sku')
                    ->get();

                if ($productos->count() !== 20) {
                    throw new RuntimeException("El catálogo demo de {$negocio->nombre} debe tener exactamente 20 productos.");
                }

                $this->limpiarHistorialAnterior($negocio, $productos, $prefijo);
                $dueno = User::query()->where('email', 'dueno.'.$clave.'@stockmype.test')->firstOrFail();
                $empleado = User::query()->where('email', 'empleado.'.$clave.'@stockmype.test')->firstOrFail();
                $planes = $this->crearPlanDeVentas($productos);
                $ventasPorProducto = $planes['cantidades'];
                $compras = [
                    $productos[8]->id => 4,
                    $productos[12]->id => 2,
                    $productos[16]->id => 3,
                ];
                $ajusteProductoId = $productos[13]->id;
                $ajuste = 1;
                $inicio = CarbonImmutable::now()->subDays(30)->startOfDay();

                foreach ($productos as $producto) {
                    $cantidadVendida = $ventasPorProducto[$producto->id] ?? 0;
                    $cantidadComprada = $compras[$producto->id] ?? 0;
                    $ajusteProducto = $producto->id === $ajusteProductoId ? $ajuste : 0;
                    $stockInicial = ProductosSeeder::stockFinalParaSku($producto->sku)
                        + $cantidadVendida
                        - $cantidadComprada
                        - $ajusteProducto;

                    if ($stockInicial < 0) {
                        throw new RuntimeException("El plan demo produciría stock inicial negativo para {$producto->sku}.");
                    }

                    $producto->stock_actual = number_format($stockInicial, 3, '.', '');
                    $producto->save();

                    $this->crearMovimiento(
                        $producto,
                        $dueno,
                        MovimientoInventario::TIPO_ENTRADA,
                        $stockInicial,
                        0,
                        $stockInicial,
                        'DEMO-SEED: Inventario inicial de demostración',
                        $inicio->setTime(8, 0)
                    );
                }

                $fechaCompra = $inicio->addDay()->setTime(8, 0);
                foreach ($productos as $producto) {
                    $cantidad = $compras[$producto->id] ?? 0;
                    if ($cantidad === 0) {
                        continue;
                    }

                    $stockAnterior = (float) $producto->fresh()->stock_actual;
                    $producto->stock_actual = number_format($stockAnterior + $cantidad, 3, '.', '');
                    $producto->save();
                    $this->crearMovimiento(
                        $producto,
                        $dueno,
                        MovimientoInventario::TIPO_ENTRADA,
                        $cantidad,
                        $stockAnterior,
                        $stockAnterior + $cantidad,
                        'DEMO-SEED: Compra a proveedor',
                        $fechaCompra
                    );
                }

                $productoAjustado = $productos->firstWhere('id', $ajusteProductoId);
                $stockAnteriorAjuste = (float) $productoAjustado->fresh()->stock_actual;
                $movimientoAjuste = $servicioMovimientos->registrar($dueno, [
                    'producto_id' => $productoAjustado->id,
                    'tipo' => MovimientoInventario::TIPO_AJUSTE,
                    'cantidad' => number_format($stockAnteriorAjuste + $ajuste, 3, '.', ''),
                    'motivo' => 'Ajuste de conteo físico de demostración.',
                ]);
                $this->actualizarFecha($movimientoAjuste, $fechaCompra->setTime(9, 0));

                $secuenciaDiaria = [];
                foreach ($planes['ventas'] as $plan) {
                    $fecha = $plan['fecha'];
                    $claveDia = $fecha->format('Ymd');
                    $secuenciaDiaria[$claveDia] = ($secuenciaDiaria[$claveDia] ?? 0) + 1;
                    $numero = 'DEMO-'.$prefijo.'-'.$claveDia.'-'.str_pad((string) $secuenciaDiaria[$claveDia], 2, '0', STR_PAD_LEFT);
                    $lineas = array_map(
                        fn (array $linea): array => [
                            'producto_id' => $linea['producto']->id,
                            'cantidad' => $linea['cantidad'],
                            'descuento' => 0,
                        ],
                        $plan['lineas']
                    );

                    $venta = $servicioVentas->registrar($empleado, [
                        'productos' => $lineas,
                        'observaciones' => 'Venta de demostración.',
                    ]);
                    $numeroOriginal = $venta->numero;
                    $venta->numero = $numero;
                    $venta->fecha_venta = $fecha;
                    $venta->save();
                    $venta->forceFill(['created_at' => $fecha, 'updated_at' => $fecha])->saveQuietly();
                    $venta->detalles()->update(['created_at' => $fecha, 'updated_at' => $fecha]);

                    MovimientoInventario::query()
                        ->where('negocio_id', $negocio->id)
                        ->where('motivo', 'Venta '.$numeroOriginal)
                        ->get()
                        ->each(function (MovimientoInventario $movimiento) use ($numero, $fecha): void {
                            $movimiento->motivo = 'Venta '.$numero;
                            $this->actualizarFecha($movimiento, $fecha);
                        });
                }

                foreach ($productos as $producto) {
                    $esperado = ProductosSeeder::stockFinalParaSku($producto->sku);
                    $actual = (float) $producto->fresh()->stock_actual;
                    if (abs($actual - $esperado) > 0.001) {
                        throw new RuntimeException("El stock final de {$producto->sku} no coincide con su historial de demostración.");
                    }
                }
            }
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Producto>  $productos
     * @return array{ventas: array<int, array{fecha: CarbonImmutable, lineas: array<int, array{producto: Producto, cantidad: int}>}>, cantidades: array<int, int>}
     */
    private function crearPlanDeVentas(Collection $productos): array
    {
        $ventas = [];
        $cantidades = [];
        $numeroVenta = 0;

        for ($diasAtras = 29; $diasAtras >= 0; $diasAtras--) {
            $dia = CarbonImmutable::now()->subDays($diasAtras)->startOfDay();
            $cantidadVentas = $dia->isWeekend() ? 3 : 1;

            for ($ventaDelDia = 0; $ventaDelDia < $cantidadVentas; $ventaDelDia++) {
                $lineas = [];
                $inicio = ($numeroVenta * 7) % $productos->count();

                for ($indiceLinea = 0; $indiceLinea < 3; $indiceLinea++) {
                    $producto = $productos[($inicio + ($indiceLinea * 7)) % $productos->count()];
                    $cantidad = ($numeroVenta + $indiceLinea) % 5 === 0 ? 2 : 1;
                    $lineas[] = ['producto' => $producto, 'cantidad' => $cantidad];
                    $cantidades[$producto->id] = ($cantidades[$producto->id] ?? 0) + $cantidad;
                }

                $ventas[] = [
                    'fecha' => $dia->setTime(11 + ($ventaDelDia * 3), 5 + ($numeroVenta % 50)),
                    'lineas' => $lineas,
                ];
                $numeroVenta++;
            }
        }

        return ['ventas' => $ventas, 'cantidades' => $cantidades];
    }

    private function limpiarHistorialAnterior(Negocio $negocio, Collection $productos, string $prefijo): void
    {
        $idsProductos = $productos->modelKeys();
        $idsAlertas = Alerta::query()->whereIn('producto_id', $idsProductos)->pluck('id');

        if ($idsAlertas->isNotEmpty()) {
            foreach ($negocio->users as $usuario) {
                $usuario->notifications()->whereIn('data->alerta_id', $idsAlertas)->delete();
            }
            Alerta::query()->whereIn('id', $idsAlertas)->delete();
        }

        Venta::query()
            ->where('negocio_id', $negocio->id)
            ->where('numero', 'like', 'DEMO-'.$prefijo.'-%')
            ->delete();

        MovimientoInventario::query()
            ->where('negocio_id', $negocio->id)
            ->whereIn('producto_id', $idsProductos)
            ->where(function ($consulta) use ($prefijo): void {
                $consulta->where('motivo', 'like', 'DEMO-SEED:%')
                    ->orWhere('motivo', 'like', 'Venta DEMO-'.$prefijo.'-%');
            })
            ->delete();
    }

    private function crearMovimiento(
        Producto $producto,
        User $usuario,
        string $tipo,
        float|int $cantidad,
        float|int $stockAnterior,
        float|int $stockResultante,
        string $motivo,
        CarbonImmutable $fecha
    ): void {
        $movimiento = MovimientoInventario::create([
            'negocio_id' => $producto->negocio_id,
            'producto_id' => $producto->id,
            'user_id' => $usuario->id,
            'tipo' => $tipo,
            'cantidad' => number_format((float) $cantidad, 3, '.', ''),
            'stock_anterior' => number_format((float) $stockAnterior, 3, '.', ''),
            'stock_resultante' => number_format((float) $stockResultante, 3, '.', ''),
            'motivo' => $motivo,
        ]);
        $this->actualizarFecha($movimiento, $fecha);
    }

    private function actualizarFecha(MovimientoInventario $movimiento, CarbonImmutable $fecha): void
    {
        $movimiento->forceFill(['created_at' => $fecha, 'updated_at' => $fecha])->saveQuietly();
    }
}
