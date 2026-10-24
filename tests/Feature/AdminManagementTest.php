<?php

namespace Tests\Feature;

use App\Models\Negocio;
use App\Models\Producto;
use App\Models\RegistroAuditoriaAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_dashboard_lists_business_and_user_management(): void
    {
        $administrador = User::factory()->administrador()->create();
        $this->assertTrue($administrador->fresh()->activo);
        $this->assertNull($administrador->fresh()->negocio_id);

        $this->actingAs($administrador)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Administración de StockMype')
            ->assertSee('Negocios')
            ->assertSee('Usuarios')
            ->assertSee('Actividad administrativa reciente')
            ->assertSee('Registrar un negocio');

        $this->actingAs($administrador)->get(route('admin.negocios.index'))->assertOk();
        $this->actingAs($administrador)->get(route('admin.usuarios.index'))->assertOk();
        $this->actingAs($administrador)->get(route('admin.auditoria.index'))->assertOk();
    }

    public function test_non_administrator_cannot_access_administration_crud(): void
    {
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);

        $this->actingAs($dueno)->get(route('admin.negocios.index'))->assertForbidden();
        $this->actingAs($dueno)->get(route('admin.usuarios.index'))->assertForbidden();
    }

    public function test_administrator_can_create_and_update_business(): void
    {
        $administrador = User::factory()->administrador()->create();

        $this->actingAs($administrador)->post(route('admin.negocios.store'), [
            'nombre' => 'Restaurante del Puerto',
            'razon_social' => 'Restaurante del Puerto SAC',
            'ruc' => '20123456789',
            'rubro' => 'gastronomia',
            'direccion' => 'Tumbes',
            'telefono' => '987654321',
        ])->assertRedirect(route('admin.negocios.index'));

        $negocio = Negocio::where('ruc', '20123456789')->firstOrFail();

        $this->actingAs($administrador)->put(route('admin.negocios.update', $negocio), [
            'nombre' => 'Restaurante actualizado',
            'ruc' => '20123456789',
            'rubro' => 'retail',
        ])->assertRedirect(route('admin.negocios.index'));

        $this->assertDatabaseHas('negocios', [
            'id' => $negocio->id,
            'nombre' => 'Restaurante actualizado',
            'rubro' => 'retail',
        ]);
        $this->assertDatabaseHas('registros_auditoria_admin', [
            'actor_id' => $administrador->id,
            'evento' => 'negocio.creado',
            'entidad_id' => $negocio->id,
        ]);
        $this->assertDatabaseHas('registros_auditoria_admin', [
            'actor_id' => $administrador->id,
            'evento' => 'negocio.actualizado',
            'entidad_id' => $negocio->id,
        ]);
    }

    public function test_business_with_registered_data_cannot_be_deleted(): void
    {
        $administrador = User::factory()->administrador()->create();
        $negocio = Negocio::factory()->create();

        $this->actingAs($administrador)->delete(route('admin.negocios.destroy', $negocio))
            ->assertRedirect()
            ->assertSessionHas('status', 'Negocio eliminado correctamente.');

        $negocioConDatos = Negocio::factory()->create();
        User::factory()->dueno()->create(['negocio_id' => $negocioConDatos->id]);

        $this->actingAs($administrador)->delete(route('admin.negocios.destroy', $negocioConDatos))
            ->assertSessionHasErrors('negocio');
        $this->assertDatabaseHas('negocios', ['id' => $negocioConDatos->id]);
    }

    public function test_administrator_can_create_owner_employee_and_unassigned_administrator(): void
    {
        $administrador = User::factory()->administrador()->create();
        $negocio = Negocio::factory()->create();

        $this->actingAs($administrador)->post(route('admin.usuarios.store'), [
            'name' => 'Dueña de prueba',
            'email' => 'duena@example.test',
            'rol' => User::ROL_DUENO,
            'negocio_id' => $negocio->id,
            'password' => 'Clave-segura-123!',
        ])->assertRedirect(route('admin.usuarios.index'));

        $this->actingAs($administrador)->post(route('admin.usuarios.store'), [
            'name' => 'Empleado de prueba',
            'email' => 'empleado@example.test',
            'rol' => User::ROL_EMPLEADO,
            'negocio_id' => $negocio->id,
            'password' => 'Clave-segura-123!',
        ])->assertRedirect(route('admin.usuarios.index'));

        $this->actingAs($administrador)->post(route('admin.usuarios.store'), [
            'name' => 'Admin adicional',
            'email' => 'admin2@example.test',
            'rol' => User::ROL_ADMINISTRADOR,
            'negocio_id' => $negocio->id,
            'password' => 'Clave-segura-123!',
        ])->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'duena@example.test',
            'rol' => User::ROL_DUENO,
            'negocio_id' => $negocio->id,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'empleado@example.test',
            'rol' => User::ROL_EMPLEADO,
            'negocio_id' => $negocio->id,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'admin2@example.test',
            'rol' => User::ROL_ADMINISTRADOR,
            'negocio_id' => null,
        ]);
    }

    public function test_administrator_cannot_assign_a_business_role_without_valid_business(): void
    {
        $administrador = User::factory()->administrador()->create();

        $this->actingAs($administrador)->from(route('admin.usuarios.create'))
            ->post(route('admin.usuarios.store'), [
                'name' => 'Sin negocio',
                'email' => 'sin-negocio@example.test',
                'rol' => User::ROL_EMPLEADO,
                'negocio_id' => 9999,
                'password' => 'Clave-segura-123!',
            ])
            ->assertRedirect(route('admin.usuarios.create'))
            ->assertSessionHasErrors('negocio_id');

        $this->assertDatabaseMissing('users', ['email' => 'sin-negocio@example.test']);
    }

    public function test_administrator_can_update_user_and_leave_password_unchanged(): void
    {
        $administrador = User::factory()->administrador()->create();
        $negocio = Negocio::factory()->create();
        $usuario = User::factory()->create([
            'name' => 'Empleado inicial',
            'negocio_id' => $negocio->id,
        ]);
        $contrasenaOriginal = $usuario->password;

        $this->actingAs($administrador)->put(route('admin.usuarios.update', $usuario), [
            'name' => 'Empleado actualizado',
            'email' => $usuario->email,
            'rol' => User::ROL_DUENO,
            'negocio_id' => $negocio->id,
            'password' => '',
        ])->assertRedirect(route('admin.usuarios.index'));

        $usuario->refresh();
        $this->assertSame('Empleado actualizado', $usuario->name);
        $this->assertSame(User::ROL_DUENO, $usuario->rol);
        $this->assertSame($contrasenaOriginal, $usuario->password);
        $this->assertTrue(Hash::check('password', $usuario->password));
    }

    public function test_administrator_cannot_delete_self_and_can_remove_another_administrator(): void
    {
        $administrador = User::factory()->administrador()->create();

        $this->actingAs($administrador)->delete(route('admin.usuarios.destroy', $administrador))
            ->assertSessionHasErrors('usuario');
        $this->assertDatabaseHas('users', ['id' => $administrador->id]);

        $administradorAdicional = User::factory()->administrador()->create();

        $this->actingAs($administrador)->delete(route('admin.usuarios.destroy', $administradorAdicional))
            ->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseHas('users', ['id' => $administrador->id]);

        $this->actingAs($administrador)->delete(route('admin.usuarios.destroy', $administrador))
            ->assertSessionHasErrors('usuario');
    }

    public function test_administrator_cannot_demote_own_role_or_move_self_to_a_business(): void
    {
        $administrador = User::factory()->administrador()->create(['name' => 'Admin inicial']);
        $negocio = Negocio::factory()->create();

        $this->actingAs($administrador)->put(route('admin.usuarios.update', $administrador), [
            'name' => 'Admin actualizado',
            'email' => $administrador->email,
            'rol' => User::ROL_EMPLEADO,
            'negocio_id' => $negocio->id,
            'password' => '',
        ])->assertRedirect(route('admin.usuarios.index'));

        $administrador->refresh();
        $this->assertSame('Admin actualizado', $administrador->name);
        $this->assertSame(User::ROL_ADMINISTRADOR, $administrador->rol);
        $this->assertNull($administrador->negocio_id);
    }

    public function test_business_and_user_form_validation_rejects_duplicate_ruc_and_email(): void
    {
        $administrador = User::factory()->administrador()->create();
        $negocioExistente = Negocio::factory()->create(['ruc' => '20987654321']);
        $usuarioExistente = User::factory()->create(['email' => 'ocupado@example.test']);

        $this->actingAs($administrador)->post(route('admin.negocios.store'), [
            'nombre' => 'Otro negocio',
            'ruc' => $negocioExistente->ruc,
            'rubro' => 'retail',
        ])->assertSessionHasErrors('ruc');

        $this->actingAs($administrador)->post(route('admin.usuarios.store'), [
            'name' => 'Otro usuario',
            'email' => $usuarioExistente->email,
            'rol' => User::ROL_EMPLEADO,
            'negocio_id' => $negocioExistente->id,
            'password' => 'Clave-segura-123!',
        ])->assertSessionHasErrors('email');
    }

    public function test_business_with_inventory_cannot_be_deleted_even_if_products_are_soft_deleted(): void
    {
        $administrador = User::factory()->administrador()->create();
        $negocio = Negocio::factory()->create();
        $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
        $producto->delete();

        $this->actingAs($administrador)->delete(route('admin.negocios.destroy', $negocio))
            ->assertSessionHasErrors('negocio');
        $this->assertDatabaseHas('negocios', ['id' => $negocio->id]);
    }

    public function test_administrator_can_suspend_and_reactivate_business_and_account_access_is_revoked(): void
    {
        $administrador = User::factory()->administrador()->create();
        $negocio = Negocio::factory()->create();
        $dueno = User::factory()->dueno()->create(['negocio_id' => $negocio->id]);

        $this->actingAs($administrador)->patch(route('admin.negocios.estado', $negocio), [
            'activo' => false,
        ])->assertRedirect(route('admin.negocios.index'));
        $this->assertFalse($negocio->fresh()->activo);
        $this->assertDatabaseHas('registros_auditoria_admin', [
            'actor_id' => $administrador->id,
            'evento' => 'negocio.suspendido',
            'entidad_id' => $negocio->id,
        ]);

        $this->actingAs($dueno)->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->actingAs($administrador)->patch(route('admin.negocios.estado', $negocio), [
            'activo' => true,
        ])->assertRedirect(route('admin.negocios.index'));

        $this->actingAs($administrador)->patch(route('admin.usuarios.estado', $dueno), [
            'activo' => false,
        ])->assertRedirect(route('admin.usuarios.index'));
        $this->assertFalse($dueno->fresh()->activo);
        $this->assertDatabaseHas('registros_auditoria_admin', [
            'evento' => 'usuario.suspendido',
            'entidad_id' => $dueno->id,
        ]);

        $this->actingAs($dueno)->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->actingAs($administrador)->patch(route('admin.usuarios.estado', $dueno), [
            'activo' => true,
        ])->assertRedirect(route('admin.usuarios.index'));
        $this->assertTrue($dueno->fresh()->activo);
    }

    public function test_administrator_cannot_suspend_self_and_can_suspend_another_admin_while_remain_active(): void
    {
        $administrador = User::factory()->administrador()->create();

        $this->actingAs($administrador)->patch(route('admin.usuarios.estado', $administrador), [
            'activo' => false,
        ])->assertSessionHasErrors('usuario');

        $this->actingAs($administrador)->patch(route('admin.usuarios.estado', $administrador), [
            'activo' => true,
        ])->assertRedirect(route('admin.usuarios.index'));

        $otroAdministrador = User::factory()->administrador()->create();
        $this->actingAs($administrador)->patch(route('admin.usuarios.estado', $otroAdministrador), [
            'activo' => false,
        ])->assertRedirect(route('admin.usuarios.index'));
        $this->assertFalse($otroAdministrador->fresh()->activo);
        $this->assertDatabaseHas('registros_auditoria_admin', [
            'evento' => 'usuario.suspendido',
            'entidad_id' => $otroAdministrador->id,
        ]);
    }

    public function test_administrator_filters_businesses_and_users_by_active_state(): void
    {
        $administrador = User::factory()->administrador()->create();
        $activo = Negocio::factory()->create(['nombre' => 'Negocio Activo']);
        $suspendido = Negocio::factory()->create(['nombre' => 'Negocio Suspendido', 'activo' => false]);
        $usuarioActivo = User::factory()->create(['negocio_id' => $activo->id, 'name' => 'Cuenta Activa']);
        $usuarioSuspendido = User::factory()->create([
            'negocio_id' => $activo->id,
            'name' => 'Cuenta Suspendida',
            'activo' => false,
        ]);

        $this->actingAs($administrador)
            ->get(route('admin.negocios.index', ['estado' => 'suspendido']))
            ->assertOk()
            ->assertSee('Negocio Suspendido')
            ->assertDontSee('Negocio Activo');

        $this->actingAs($administrador)
            ->get(route('admin.usuarios.index', ['estado' => 'activo']))
            ->assertOk()
            ->assertSee($usuarioActivo->name)
            ->assertDontSee($usuarioSuspendido->name);

        $this->assertSame(0, RegistroAuditoriaAdmin::where('evento', 'usuario.suspendido')
            ->where('entidad_id', $usuarioSuspendido->id)->count());
    }
}
