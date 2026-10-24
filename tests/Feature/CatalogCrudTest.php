<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\CatalogoDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CatalogCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_manage_categories_only_inside_their_business(): void
    {
        $negocio = Negocio::factory()->create();
        $otroNegocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $categoriaAjena = Categoria::factory()->create(['negocio_id' => $otroNegocio->id]);

        $this->actingAs($dueno)->get(route('categorias.index'))
            ->assertOk()
            ->assertDontSee($categoriaAjena->nombre);

        $this->actingAs($dueno)->post(route('categorias.store'), [
            'nombre' => 'Bebidas',
            'descripcion' => 'Bebidas frías y calientes',
            'negocio_id' => $otroNegocio->id,
        ])->assertRedirect(route('categorias.index'));

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Bebidas',
            'negocio_id' => $negocio->id,
        ]);

        $this->actingAs($dueno)->get(route('categorias.edit', $categoriaAjena))->assertNotFound();
    }

    public function test_category_name_must_be_unique_per_business_and_cannot_delete_a_used_category(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $categoria = Categoria::factory()->create([
            'negocio_id' => $negocio->id,
            'nombre' => 'Abarrotes',
        ]);

        $this->actingAs($dueno)->post(route('categorias.store'), [
            'nombre' => 'Abarrotes',
        ])->assertSessionHasErrors('nombre');

        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'categoria_id' => $categoria->id,
        ]);
        $producto->delete();

        $this->actingAs($dueno)->delete(route('categorias.destroy', $categoria))
            ->assertSessionHasErrors('categoria');
        $this->assertDatabaseHas('categorias', ['id' => $categoria->id]);
    }

    public function test_owner_can_create_and_update_provider_and_delete_is_blocked_when_used(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);

        $this->actingAs($dueno)->post(route('proveedores.store'), [
            'nombre' => 'Distribuidora del Norte',
            'ruc' => '20123456789',
            'email' => 'ventas@example.com',
            'telefono' => '987654321',
        ])->assertRedirect(route('proveedores.index'));

        $proveedor = Proveedor::where('negocio_id', $negocio->id)->firstOrFail();
        $this->assertSame('Distribuidora del Norte', $proveedor->nombre);

        $this->actingAs($dueno)->put(route('proveedores.update', $proveedor), [
            'nombre' => 'Distribuidora Norte Actualizada',
            'ruc' => '20123456789',
        ])->assertRedirect(route('proveedores.index'));

        $this->assertDatabaseHas('proveedores', [
            'id' => $proveedor->id,
            'nombre' => 'Distribuidora Norte Actualizada',
        ]);

        Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'proveedor_id' => $proveedor->id,
        ])->delete();

        $this->actingAs($dueno)->delete(route('proveedores.destroy', $proveedor))
            ->assertSessionHasErrors('proveedor');
    }

    public function test_employee_can_consult_products_but_cannot_manage_catalogs(): void
    {
        $negocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

        $this->actingAs($empleado)->get(route('productos.index'))->assertOk();
        $this->actingAs($empleado)->get(route('productos.show', $producto))->assertOk();
        $this->actingAs($empleado)->get(route('productos.create'))->assertForbidden();
        $this->actingAs($empleado)->get(route('categorias.index'))->assertForbidden();
        $this->actingAs($empleado)->get(route('proveedores.index'))->assertForbidden();
    }

    public function test_employees_cannot_change_products_even_through_authorization_policy(): void
    {
        $negocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

        $this->assertFalse(Gate::forUser($empleado)->allows('update', $producto));
        $this->assertFalse(Gate::forUser($empleado)->allows('delete', $producto));
    }

    public function test_product_crud_validates_foreign_category_and_provider_ids(): void
    {
        $negocio = Negocio::factory()->create();
        $otroNegocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $categoriaAjena = Categoria::factory()->create(['negocio_id' => $otroNegocio->id]);
        $proveedorAjeno = Proveedor::factory()->create(['negocio_id' => $otroNegocio->id]);
        $productoAjeno = Producto::factory()->create(['negocio_id' => $otroNegocio->id]);

        $datosProducto = [
            'sku' => 'arroz-001',
            'nombre' => 'Arroz extra',
            'precio_compra' => '3.50',
            'precio_venta' => '4.50',
            'stock_actual' => '10.000',
            'stock_minimo' => '2.000',
            'unidad_medida' => 'kg',
            'categoria_id' => $categoriaAjena->id,
            'proveedor_id' => $proveedorAjeno->id,
        ];

        $this->actingAs($dueno)->post(route('productos.store'), $datosProducto)
            ->assertSessionHasErrors(['categoria_id', 'proveedor_id']);
        $this->assertDatabaseMissing('productos', ['sku' => 'ARROZ-001']);

        unset($datosProducto['categoria_id'], $datosProducto['proveedor_id']);

        $this->actingAs($dueno)->post(route('productos.store'), $datosProducto)
            ->assertRedirect(route('productos.index'));
        $this->assertDatabaseHas('productos', [
            'negocio_id' => $negocio->id,
            'sku' => 'ARROZ-001',
            'nombre' => 'Arroz extra',
        ]);

        $producto = Producto::where('sku', 'ARROZ-001')->firstOrFail();
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'tipo' => 'entrada',
            'cantidad' => '10.000',
            'stock_anterior' => '0.000',
            'stock_resultante' => '10.000',
            'motivo' => 'Stock inicial al crear el producto',
        ]);
        $this->actingAs($dueno)->get(route('productos.index', ['buscar' => 'ARROZ-001']))
            ->assertOk()
            ->assertSee('Arroz extra');
        $this->actingAs($dueno)->get(route('productos.edit', $productoAjeno))->assertNotFound();
        $this->actingAs($dueno)->delete(route('productos.destroy', $producto))
            ->assertRedirect(route('productos.index'));
        $this->assertSoftDeleted($producto);
    }

    public function test_demo_catalog_seeder_creates_examples_for_three_business_types_and_is_repeatable(): void
    {
        $this->seed(CatalogoDemoSeeder::class);
        $this->seed(CatalogoDemoSeeder::class);

        $this->assertDatabaseCount('negocios', 3);
        $this->assertDatabaseCount('categorias', 9);
        $this->assertDatabaseCount('proveedores', 6);
        $this->assertDatabaseCount('productos', 9);
        $this->assertDatabaseHas('negocios', [
            'nombre' => 'Restaurante Sabor Tumbesino (Demo)',
            'rubro' => 'gastronomia',
        ]);
        $this->assertDatabaseHas('productos', [
            'sku' => 'DEMO-BOD-001',
            'stock_actual' => 3,
        ]);
    }
}
