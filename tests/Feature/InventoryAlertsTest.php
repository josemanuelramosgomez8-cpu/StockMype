<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\User;
use App\Notifications\AlertaInventarioNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class InventoryAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_observer_creates_low_stock_and_exhausted_alerts_without_scheduler_duplicates(): void
    {
        $negocio = Negocio::factory()->create();
        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'stock_actual' => '2.000',
            'stock_minimo' => '5.000',
        ]);

        $this->assertDatabaseHas('alertas', [
            'negocio_id' => $negocio->id,
            'producto_id' => $producto->id,
            'tipo' => Alerta::TIPO_STOCK_BAJO,
            'estado' => Alerta::ESTADO_PENDIENTE,
            'resuelta_at' => null,
        ]);

        Artisan::call('inventario:revisar-alertas');
        Artisan::call('inventario:revisar-alertas');
        $this->assertSame(1, Alerta::where('producto_id', $producto->id)->count());

        $producto->update(['stock_actual' => '0.000']);
        $this->assertDatabaseHas('alertas', [
            'producto_id' => $producto->id,
            'tipo' => Alerta::TIPO_AGOTADO,
            'estado' => Alerta::ESTADO_PENDIENTE,
            'resuelta_at' => null,
        ]);
        $this->assertDatabaseHas('alertas', [
            'producto_id' => $producto->id,
            'tipo' => Alerta::TIPO_STOCK_BAJO,
            'estado' => Alerta::ESTADO_LEIDA,
        ]);
    }

    public function test_resolved_stock_alert_can_be_reopened_as_a_new_alert_cycle(): void
    {
        $negocio = Negocio::factory()->create();
        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'stock_actual' => '1.000',
            'stock_minimo' => '5.000',
        ]);

        $producto->update(['stock_actual' => '8.000']);
        $this->assertDatabaseHas('alertas', [
            'producto_id' => $producto->id,
            'tipo' => Alerta::TIPO_STOCK_BAJO,
            'estado' => Alerta::ESTADO_LEIDA,
        ]);

        $producto->update(['stock_actual' => '2.000']);
        $this->assertSame(2, Alerta::where('producto_id', $producto->id)
            ->where('tipo', Alerta::TIPO_STOCK_BAJO)->count());
        $this->assertSame(1, Alerta::where('producto_id', $producto->id)
            ->where('tipo', Alerta::TIPO_STOCK_BAJO)
            ->whereNull('resuelta_at')
            ->where('estado', Alerta::ESTADO_PENDIENTE)
            ->count());
    }

    public function test_hourly_command_creates_upcoming_expiration_alert_and_alerts_are_tenant_scoped(): void
    {
        $negocio = Negocio::factory()->create();
        $otroNegocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create([
            'negocio_id' => $negocio->id,
            'stock_actual' => '20.000',
            'stock_minimo' => '2.000',
            'fecha_vencimiento' => today()->addDays(3),
        ]);
        $productoAjeno = Producto::factory()->create([
            'negocio_id' => $otroNegocio->id,
            'stock_actual' => '20.000',
            'stock_minimo' => '2.000',
            'fecha_vencimiento' => today()->addDays(4),
        ]);

        Artisan::call('inventario:revisar-alertas');

        $this->assertDatabaseHas('alertas', [
            'producto_id' => $producto->id,
            'tipo' => Alerta::TIPO_POR_VENCER,
        ]);
        $this->assertDatabaseHas('alertas', [
            'producto_id' => $productoAjeno->id,
            'tipo' => Alerta::TIPO_POR_VENCER,
        ]);

        $this->actingAs($dueno)->get(route('alertas.index'))
            ->assertOk()
            ->assertSee($producto->nombre)
            ->assertDontSee($productoAjeno->nombre);
    }

    public function test_business_user_can_mark_own_alert_and_notification_read_but_not_another_tenant_alert(): void
    {
        $negocio = Negocio::factory()->create();
        $otroNegocio = Negocio::factory()->create();
        $empleado = User::factory()->create(['negocio_id' => $negocio->id]);
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
        $alerta = Alerta::create([
            'negocio_id' => $negocio->id,
            'producto_id' => $producto->id,
            'tipo' => Alerta::TIPO_STOCK_BAJO,
            'estado' => Alerta::ESTADO_PENDIENTE,
            'mensaje' => 'Stock bajo de prueba.',
        ]);
        $empleado->notify(new AlertaInventarioNotification($alerta));

        $alertaAjena = Alerta::create([
            'negocio_id' => $otroNegocio->id,
            'tipo' => Alerta::TIPO_AGOTADO,
            'estado' => Alerta::ESTADO_PENDIENTE,
            'mensaje' => 'Alerta ajena.',
        ]);

        $this->assertSame(1, $empleado->unreadNotifications()->count());
        $this->actingAs($empleado)->patch(route('alertas.leida', $alerta))
            ->assertRedirect(route('alertas.index'));
        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => Alerta::ESTADO_LEIDA,
        ]);
        $this->assertSame(0, $empleado->fresh()->unreadNotifications()->count());
        $this->actingAs($empleado)->patch(route('alertas.leida', $alertaAjena))->assertNotFound();
    }
}
