<?php

namespace Tests\Feature\Auth;

use App\Models\Negocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('Nombre del negocio')
            ->assertSee('Gastronomía');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'negocio_nombre' => 'Restaurante El Norte',
            'rubro' => 'gastronomia',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('negocios', [
            'nombre' => 'Restaurante El Norte',
            'rubro' => 'gastronomia',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'rol' => User::ROL_DUENO,
        ]);
        $this->assertSame(1, Negocio::count());
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_cannot_assign_privileged_role_or_business(): void
    {
        $this->post('/register', [
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'negocio_nombre' => 'My business',
            'rubro' => 'retail',
            'rol' => User::ROL_ADMINISTRADOR,
            'negocio_id' => 999,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'attacker@example.com',
            'rol' => User::ROL_DUENO,
        ]);
        $this->assertDatabaseMissing('negocios', ['id' => 999]);
    }

    public function test_registration_requires_valid_business_data(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'negocio_nombre' => '',
            'rubro' => 'otro',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['negocio_nombre', 'rubro']);
        $this->assertDatabaseMissing('users', ['email' => 'invalid@example.com']);
    }
}
