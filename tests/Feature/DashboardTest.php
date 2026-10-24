<?php

namespace Tests\Feature;

use App\Models\DetalleVenta;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_business_scoped_metrics_sales_chart_top_products_and_critical_stock(): void
    {
        $negocio = Negocio::factory()->create();
        $otroNegocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);

        $productoCritico = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Arroz crítico',
            'stock_actual' => '2.000',
            'stock_minimo' => '5.000',
        ]);
        $productoMasVendido = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Ceviche de prueba',
            'stock_actual' => '20.000',
        ]);
        $productoAjeno = Producto::factory()->create([
            'negocio_id' => $otroNegocio->id,
            'nombre' => 'Producto de otro negocio',
            'stock_actual' => '0.000',
        ]);

        $ventaHoy = $this->crearVenta($negocio, $dueno, now(), 'V-DASH-001', '25.00');
        $this->crearDetalle($ventaHoy, $productoMasVendido, '2.000', '10.00', '20.00');
        $this->crearDetalle($ventaHoy, $productoCritico, '1.000', '5.00', '5.00');

        $ventaHaceCincoDias = $this->crearVenta($negocio, $dueno, now()->subDays(5), 'V-DASH-002', '12.50');
        $this->crearDetalle($ventaHaceCincoDias, $productoMasVendido, '1.000', '10.00', '10.00');

        $ventaAjena = $this->crearVenta($otroNegocio, null, now(), 'V-DASH-003', '900.00');
        $this->crearDetalle($ventaAjena, $productoAjeno, '90.000', '10.00', '900.00');

        $this->actingAs($dueno)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ventas de los últimos 7 días')
            ->assertSee('Ceviche de prueba')
            ->assertSee('Arroz crítico')
            ->assertDontSee('Producto de otro negocio')
            ->assertViewHas('totalProductos', 2)
            ->assertViewHas('alertasPendientes', 1)
            ->assertViewHas('montoVentasHoy', 25.0)
            ->assertViewHas('cantidadVentasHoy', 1)
            ->assertViewHas('montoVentasMes', 37.5)
            ->assertViewHas('cantidadVentasMes', 2)
            ->assertViewHas('totalProductosCriticos', 1)
            ->assertViewHas('productosMasVendidos', function ($productos): bool {
                return $productos->first()->nombre_producto === 'Ceviche de prueba'
                    && (float) $productos->first()->cantidad_vendida === 3.0;
            })
            ->assertViewHas('ventasPorDia', function ($ventasPorDia): bool {
                return $ventasPorDia->count() === 7
                    && $ventasPorDia->sum('total') === 37.5;
            });
    }

    public function test_employee_can_open_dashboard_and_critical_products_include_upcoming_expiration(): void
    {
        $negocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $productoPorVencer = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Leche por vencer',
            'stock_actual' => '20.000',
            'stock_minimo' => '2.000',
            'fecha_vencimiento' => today()->addDays(3),
        ]);

        $this->actingAs($empleado)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($productoPorVencer->nombre)
            ->assertSee('Por vencer')
            ->assertViewHas('totalProductosCriticos', 1);
    }

    private function crearVenta(
        Negocio $negocio,
        ?User $usuario,
        \DateTimeInterface $fecha,
        string $numero,
        string $total
    ): Venta {
        return Venta::create([
            'negocio_id' => $negocio->id,
            'user_id' => $usuario?->id,
            'numero' => $numero,
            'subtotal' => $total,
            'descuento' => '0.00',
            'total' => $total,
            'estado' => 'completada',
            'fecha_venta' => $fecha,
        ]);
    }

    private function crearDetalle(
        Venta $venta,
        Producto $producto,
        string $cantidad,
        string $precio,
        string $subtotal
    ): DetalleVenta {
        return DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'nombre_producto' => $producto->nombre,
            'sku' => $producto->sku,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'descuento' => '0.00',
            'subtotal' => $subtotal,
        ]);
    }
}
