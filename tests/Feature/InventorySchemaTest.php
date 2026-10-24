<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventorySchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_and_sales_tables_are_created(): void
    {
        foreach ([
            'negocios',
            'categorias',
            'proveedores',
            'productos',
            'movimientos_inventario',
            'ventas',
            'detalle_ventas',
            'alertas',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Falta la tabla {$table}.");
        }

        $this->assertTrue(Schema::hasColumns('productos', [
            'negocio_id',
            'sku',
            'precio_compra',
            'precio_venta',
            'stock_actual',
            'stock_minimo',
            'fecha_vencimiento',
            'deleted_at',
        ]));
    }

    public function test_product_is_related_to_its_business_and_category(): void
    {
        $negocio = Negocio::create([
            'nombre' => 'Bodega de prueba',
            'rubro' => 'retail',
        ]);
        $categoria = Categoria::create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Abarrotes',
        ]);
        $producto = Producto::create([
            'negocio_id' => $negocio->id,
            'categoria_id' => $categoria->id,
            'sku' => 'ARROZ-001',
            'nombre' => 'Arroz',
            'precio_compra' => 4.50,
            'precio_venta' => 5.50,
            'stock_actual' => 12.250,
            'stock_minimo' => 3,
        ]);

        $this->assertTrue($producto->negocio->is($negocio));
        $this->assertTrue($producto->categoria->is($categoria));
        $this->assertSame('12.250', $producto->stock_actual);

        $producto->delete();

        $this->assertSoftDeleted($producto);
    }
}
