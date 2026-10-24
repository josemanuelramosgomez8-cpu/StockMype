<?php

namespace Tests\Feature;

use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class InventorySalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_record_inventory_entry_exit_and_adjustment_with_auditable_stock(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'stock_actual' => '5.000',
        ]);

        $this->actingAs($dueno)->post(route('movimientos.store'), [
            'producto_id' => $producto->id,
            'tipo' => 'entrada',
            'cantidad' => '3.250',
            'motivo' => 'Compra al proveedor',
        ])->assertRedirect();

        $this->assertSame('8.250', $producto->fresh()->stock_actual);

        $this->actingAs($dueno)->post(route('movimientos.store'), [
            'producto_id' => $producto->id,
            'tipo' => 'salida',
            'cantidad' => '1.250',
            'motivo' => 'Merma',
        ])->assertRedirect();

        $this->assertSame('7.000', $producto->fresh()->stock_actual);

        $this->actingAs($dueno)->post(route('movimientos.store'), [
            'producto_id' => $producto->id,
            'tipo' => 'ajuste',
            'cantidad' => '6.500',
            'motivo' => 'Conteo físico',
        ])->assertRedirect();

        $this->assertSame('6.500', $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'tipo' => 'ajuste',
            'cantidad' => '0.500',
            'stock_anterior' => '7.000',
            'stock_resultante' => '6.500',
            'motivo' => 'Conteo físico',
            'user_id' => $dueno->id,
        ]);
    }

    public function test_inventory_movements_cannot_cross_businesses_or_make_stock_negative(): void
    {
        $negocio = Negocio::factory()->create();
        $otroNegocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $productoPropio = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'stock_actual' => '1.000',
        ]);
        $productoAjeno = Producto::factory()->create(['negocio_id' => $otroNegocio->id]);

        $this->actingAs($dueno)->post(route('movimientos.store'), [
            'producto_id' => $productoAjeno->id,
            'tipo' => 'entrada',
            'cantidad' => '2.000',
            'motivo' => 'Intento ajeno',
        ])->assertSessionHasErrors('producto_id');

        $this->actingAs($dueno)->post(route('movimientos.store'), [
            'producto_id' => $productoPropio->id,
            'tipo' => 'salida',
            'cantidad' => '2.000',
            'motivo' => 'Exceso',
        ])->assertSessionHasErrors('cantidad');

        $this->assertSame('1.000', $productoPropio->fresh()->stock_actual);
        $this->assertDatabaseCount('movimientos_inventario', 0);
    }

    public function test_employee_can_register_sale_and_server_uses_catalog_price_and_records_stock_movement(): void
    {
        $negocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'precio_venta' => '4.50',
            'stock_actual' => '10.000',
        ]);

        $this->actingAs($empleado)->post(route('ventas.store'), [
            'productos' => [[
                'producto_id' => $producto->id,
                'cantidad' => '2.500',
                'descuento' => '1.00',
                'precio_unitario' => '0.01',
            ]],
            'observaciones' => 'Pedido para llevar',
        ])->assertRedirect();

        $venta = Venta::query()->where('negocio_id', $negocio->id)->firstOrFail();
        $this->assertSame('11.25', $venta->subtotal);
        $this->assertSame('1.00', $venta->descuento);
        $this->assertSame('10.25', $venta->total);
        $this->assertSame($empleado->id, $venta->user_id);
        $this->assertSame('7.500', $producto->fresh()->stock_actual);
        $this->assertDatabaseHas('detalle_ventas', [
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'precio_unitario' => '4.50',
            'cantidad' => '2.500',
            'subtotal' => '10.25',
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'tipo' => 'salida',
            'cantidad' => '2.500',
            'stock_anterior' => '10.000',
            'stock_resultante' => '7.500',
            'user_id' => $empleado->id,
        ]);
    }

    public function test_sale_fails_atomically_for_insufficient_stock_and_does_not_accept_foreign_products(): void
    {
        $negocio = Negocio::factory()->create();
        $otroNegocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'stock_actual' => '1.000',
        ]);
        $productoAjeno = Producto::factory()->create(['negocio_id' => $otroNegocio->id]);

        $this->actingAs($empleado)->post(route('ventas.store'), [
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => '1.000'],
                ['producto_id' => $producto->id, 'cantidad' => '1.000'],
            ],
        ])->assertSessionHasErrors('productos');
        $this->assertDatabaseCount('ventas', 0);

        $this->actingAs($empleado)->post(route('ventas.store'), [
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => '1.000'],
                ['producto_id' => Producto::factory()->create([
                    'negocio_id' => $negocio->id,
                    'stock_actual' => '0.000',
                ])->id, 'cantidad' => '1.000'],
            ],
        ])->assertSessionHasErrors('productos');

        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('detalle_ventas', 0);
        $this->assertDatabaseCount('movimientos_inventario', 0);
        $this->assertSame('1.000', $producto->fresh()->stock_actual);

        $this->actingAs($empleado)->post(route('ventas.store'), [
            'productos' => [['producto_id' => $productoAjeno->id, 'cantidad' => '1.000']],
        ])->assertSessionHasErrors('productos.0.producto_id');
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_employee_can_view_sales_but_only_owner_can_record_inventory_movements(): void
    {
        $negocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
        $venta = Venta::create([
            'negocio_id' => $negocio->id,
            'user_id' => $empleado->id,
            'numero' => 'V-TEST-001',
            'subtotal' => '10.00',
            'descuento' => '0.00',
            'total' => '10.00',
            'estado' => 'completada',
            'fecha_venta' => now(),
        ]);
        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'nombre_producto' => $producto->nombre,
            'sku' => $producto->sku,
            'cantidad' => '1.000',
            'precio_unitario' => '10.00',
            'descuento' => '0.00',
            'subtotal' => '10.00',
        ]);
        $otroNegocio = Negocio::factory()->create();
        $ventaAjena = Venta::create([
            'negocio_id' => $otroNegocio->id,
            'numero' => 'V-TEST-002',
            'subtotal' => '10.00',
            'descuento' => '0.00',
            'total' => '10.00',
            'estado' => 'completada',
            'fecha_venta' => now(),
        ]);

        $this->actingAs($empleado)->get(route('ventas.index'))->assertOk();
        $this->actingAs($empleado)->get(route('ventas.show', $venta))->assertOk();
        $this->actingAs($empleado)->get(route('ventas.show', $ventaAjena))->assertNotFound();
        $this->actingAs($empleado)->get(route('movimientos.index'))->assertForbidden();

        $this->assertTrue(Gate::forUser($empleado)->allows('viewAny', Venta::class));
        $this->assertFalse(Gate::forUser($empleado)->allows('create', MovimientoInventario::class));
    }

    public function test_owner_cannot_change_product_stock_without_an_inventory_movement(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'stock_actual' => '4.000',
        ]);
        $datos = [
            'sku' => $producto->sku,
            'nombre' => $producto->nombre,
            'precio_compra' => $producto->precio_compra,
            'precio_venta' => $producto->precio_venta,
            'stock_actual' => '90.000',
            'stock_minimo' => '2.000',
            'unidad_medida' => $producto->unidad_medida,
        ];

        $this->actingAs($dueno)->put(route('productos.update', $producto), $datos)
            ->assertSessionHasErrors('stock_actual');
        $this->assertSame('4.000', $producto->fresh()->stock_actual);

        unset($datos['stock_actual']);
        $this->actingAs($dueno)->put(route('productos.update', $producto), $datos)
            ->assertRedirect(route('productos.index'));
        $this->assertSame('4.000', $producto->fresh()->stock_actual);
    }

    public function test_inventory_and_sales_screens_render_for_authorized_business_users(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'stock_actual' => '5.000',
        ]);

        $this->actingAs($dueno)->get(route('movimientos.index'))->assertOk();
        $this->actingAs($dueno)->get(route('movimientos.create'))->assertOk();
        $this->actingAs($empleado)->get(route('ventas.index'))->assertOk();
        $this->actingAs($empleado)->get(route('ventas.create'))->assertOk();

        $this->actingAs($dueno)->post(route('movimientos.store'), [
            'producto_id' => $producto->id,
            'tipo' => 'entrada',
            'cantidad' => '1.000',
            'motivo' => 'Prueba de pantalla',
        ])->assertRedirect();
        $movimiento = MovimientoInventario::query()->firstOrFail();
        $this->actingAs($dueno)->get(route('movimientos.show', $movimiento))->assertOk();
    }
}
