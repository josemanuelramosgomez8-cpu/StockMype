<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TenantAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_only_view_records_from_their_own_business(): void
    {
        $negocioA = Negocio::factory()->create();
        $negocioB = Negocio::factory()->create();
        $usuario = User::factory()->dueno()->create(['negocio_id' => $negocioA->id]);
        $productoPropio = Producto::factory()->create(['negocio_id' => $negocioA->id]);
        $productoAjeno = Producto::factory()->create(['negocio_id' => $negocioB->id]);

        $this->assertTrue(Gate::forUser($usuario)->allows('view', $productoPropio));
        $this->assertFalse(Gate::forUser($usuario)->allows('view', $productoAjeno));
        $this->assertTrue(Gate::forUser($usuario)->allows('update', $productoPropio));
        $this->assertFalse(Gate::forUser($usuario)->allows('update', $productoAjeno));
    }

    public function test_employee_can_create_sales_but_cannot_manage_products(): void
    {
        $negocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
        $categoria = Categoria::factory()->create(['negocio_id' => $negocio->id]);
        $venta = new Venta(['negocio_id' => $negocio->id]);

        $this->assertTrue(Gate::forUser($empleado)->allows('view', $producto));
        $this->assertTrue(Gate::forUser($empleado)->allows('viewAny', Producto::class));
        $this->assertFalse(Gate::forUser($empleado)->allows('view', $categoria));
        $this->assertFalse(Gate::forUser($empleado)->allows('create', $producto));
        $this->assertTrue(Gate::forUser($empleado)->allows('create', $venta));
        $this->assertFalse(Gate::forUser($empleado)->allows('viewAny', Categoria::class));
        $this->assertFalse(Gate::forUser($empleado)->allows('delete', $categoria));
    }

    public function test_administrator_can_manage_businesses_but_does_not_bypass_tenant_data_policy(): void
    {
        $negocio = Negocio::factory()->create();
        $administrador = User::factory()->administrador()->create();
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

        $this->assertTrue(Gate::forUser($administrador)->allows('view', $negocio));
        $this->assertTrue(Gate::forUser($administrador)->allows('viewAny', Negocio::class));
        $this->assertFalse(Gate::forUser($administrador)->allows('view', $producto));
    }

    public function test_business_and_role_middleware_protect_dashboard_routes(): void
    {
        $dueno = User::factory()->dueno()->create(['negocio_id' => null]);
        $administrador = User::factory()->administrador()->create();

        $this->actingAs($dueno)->get('/dashboard')->assertForbidden();
        $this->actingAs($dueno)->get('/admin')->assertForbidden();
        $this->actingAs($administrador)->get('/dashboard')->assertForbidden();
        $this->actingAs($administrador)->get('/admin')->assertOk();
    }
}
