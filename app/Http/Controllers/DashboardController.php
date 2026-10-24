<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Producto;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $negocio = $request->user()->negocio;
        $hoy = CarbonImmutable::today();
        $inicioMes = $hoy->startOfMonth();
        $finMes = $hoy->endOfMonth();
        $inicioGrafico = $hoy->subDays(6);

        $ventasDelDia = $negocio->ventas()
            ->where('estado', 'completada')
            ->whereBetween('fecha_venta', [$hoy->startOfDay(), $hoy->endOfDay()]);

        $ventasDelMes = $negocio->ventas()
            ->where('estado', 'completada')
            ->whereBetween('fecha_venta', [$inicioMes->startOfDay(), $finMes->endOfDay()]);

        $ventasAgrupadas = $negocio->ventas()
            ->where('estado', 'completada')
            ->whereBetween('fecha_venta', [$inicioGrafico->startOfDay(), $hoy->endOfDay()])
            ->selectRaw('DATE(fecha_venta) as fecha, SUM(total) as total')
            ->groupBy('fecha')
            ->get()
            ->keyBy('fecha');

        $ventasPorDia = collect(range(0, 6))
            ->map(function (int $dias) use ($inicioGrafico, $ventasAgrupadas): array {
                $fecha = $inicioGrafico->addDays($dias);
                $registro = $ventasAgrupadas->get($fecha->toDateString());

                return [
                    'fecha' => $fecha->format('d/m'),
                    'total' => (float) ($registro?->total ?? 0),
                ];
            })
            ->values();

        $productosMasVendidos = DB::table('detalle_ventas')
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.negocio_id', $negocio->id)
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$inicioMes->startOfDay(), $finMes->endOfDay()])
            ->select([
                'detalle_ventas.nombre_producto',
                'detalle_ventas.sku',
            ])
            ->selectRaw('SUM(detalle_ventas.cantidad) as cantidad_vendida')
            ->selectRaw('SUM(detalle_ventas.subtotal) as ingresos')
            ->groupBy('detalle_ventas.nombre_producto', 'detalle_ventas.sku')
            ->orderByDesc('cantidad_vendida')
            ->limit(5)
            ->get();

        $productosCriticos = $this->consultaProductosCriticos($negocio->id)
            ->orderByRaw('CASE WHEN stock_actual <= 0 THEN 0 ELSE 1 END')
            ->orderBy('stock_actual')
            ->orderBy('fecha_vencimiento')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'totalProductos' => $negocio->productos()->where('activo', true)->count(),
            'alertasPendientes' => $negocio->alertas()
                ->where('estado', Alerta::ESTADO_PENDIENTE)
                ->whereNull('resuelta_at')
                ->count(),
            'montoVentasHoy' => (float) $ventasDelDia->sum('total'),
            'cantidadVentasHoy' => $ventasDelDia->count(),
            'montoVentasMes' => (float) $ventasDelMes->sum('total'),
            'cantidadVentasMes' => $ventasDelMes->count(),
            'ventasPorDia' => $ventasPorDia,
            'productosMasVendidos' => $productosMasVendidos,
            'productosCriticos' => $productosCriticos,
            'totalProductosCriticos' => $this->consultaProductosCriticos($negocio->id)->count(),
        ]);
    }

    private function consultaProductosCriticos(int $negocioId): Builder
    {
        return Producto::query()
            ->where('negocio_id', $negocioId)
            ->where('activo', true)
            ->where(function (Builder $consulta): void {
                $consulta->whereColumn('stock_actual', '<=', 'stock_minimo')
                    ->orWhere(function (Builder $vencimiento): void {
                        $vencimiento->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<=', today()->addDays(7));
                    });
            });
    }
}
