<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_filter_sales_report_by_date_and_only_see_their_business(): void
    {
        $negocio = Negocio::factory()->create();
        $otroNegocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $ventaIncluida = $this->crearVenta($negocio, $dueno, today()->setTime(10, 0), 'V-REP-001', '45.50');
        $this->crearVenta($negocio, $dueno, today()->subDays(5), 'V-REP-002', '30.00');
        $this->crearVenta($otroNegocio, null, today()->setTime(11, 0), 'V-REP-AJENA', '900.00');

        $this->actingAs($dueno)
            ->get(route('reportes.index', [
                'tipo' => 'ventas',
                'desde' => today()->toDateString(),
                'hasta' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($ventaIncluida->numero)
            ->assertDontSee('V-REP-002')
            ->assertDontSee('V-REP-AJENA')
            ->assertViewHas('resumen', function (array $resumen): bool {
                return $resumen['cantidad'] === 1 && $resumen['total'] === 45.5;
            });
    }

    public function test_employee_can_filter_inventory_movement_report_by_date(): void
    {
        $negocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
        $movimiento = $this->crearMovimiento($negocio, $empleado, $producto, 'REP-ENTRADA', today());
        $this->crearMovimiento($negocio, $empleado, $producto, 'REP-ANTIGUO', today()->subDays(3));

        $this->actingAs($empleado)
            ->get(route('reportes.index', [
                'tipo' => 'inventario',
                'desde' => today()->toDateString(),
                'hasta' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($movimiento->motivo)
            ->assertDontSee('REP-ANTIGUO')
            ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['cantidad'] === 1);
    }

    public function test_report_dates_and_type_are_validated(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);

        $this->actingAs($dueno)
            ->get(route('reportes.index', [
                'tipo' => 'otro',
                'desde' => '2026-10-20',
                'hasta' => '2026-10-01',
            ]))
            ->assertSessionHasErrors(['tipo', 'hasta']);
    }

    public function test_report_defaults_to_sales_and_current_month_when_filters_are_omitted(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);

        $this->actingAs($dueno)
            ->get(route('reportes.index'))
            ->assertOk()
            ->assertViewHas('tipo', 'ventas')
            ->assertViewHas('desde', today()->startOfMonth()->toDateString())
            ->assertViewHas('hasta', today()->toDateString());
    }

    public function test_sales_report_downloads_pdf_and_excel(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $this->crearVenta($negocio, $dueno, today()->setTime(12, 0), 'V-EXPORT-001', '15.00');
        $filtros = [
            'tipo' => 'ventas',
            'desde' => today()->toDateString(),
            'hasta' => today()->toDateString(),
        ];

        $this->actingAs($dueno)
            ->get(route('reportes.pdf', $filtros))
            ->assertDownload()
            ->assertHeader('content-type', 'application/pdf')
            ->assertSee('%PDF');

        $this->actingAs($dueno)
            ->get(route('reportes.excel', $filtros))
            ->assertDownload()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_inventory_report_downloads_pdf_and_excel(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
        $this->crearMovimiento($negocio, $dueno, $producto, 'Compra de prueba', today());
        $filtros = [
            'tipo' => 'inventario',
            'desde' => today()->toDateString(),
            'hasta' => today()->toDateString(),
        ];

        $this->actingAs($dueno)
            ->get(route('reportes.pdf', $filtros))
            ->assertDownload()
            ->assertHeader('content-type', 'application/pdf')
            ->assertSee('%PDF');

        $this->actingAs($dueno)
            ->get(route('reportes.excel', $filtros))
            ->assertDownload()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
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

    private function crearMovimiento(
        Negocio $negocio,
        User $usuario,
        Producto $producto,
        string $motivo,
        \DateTimeInterface $fecha
    ): MovimientoInventario {
        $movimiento = MovimientoInventario::create([
            'negocio_id' => $negocio->id,
            'producto_id' => $producto->id,
            'user_id' => $usuario->id,
            'tipo' => 'entrada',
            'cantidad' => '2.000',
            'stock_anterior' => '0.000',
            'stock_resultante' => '2.000',
            'motivo' => $motivo,
        ]);
        $movimiento->created_at = $fecha;
        $movimiento->save();

        return $movimiento;
    }
}
