<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_administrator_is_redirected_to_administration_after_login(): void
    {
        $administrador = User::factory()->administrador()->create();

        $this->get('/dashboard');

        $response = $this->post('/login', [
            'email' => $administrador->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($administrador);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_authenticated_administrator_is_redirected_to_admin_panel_from_login_and_registration_pages(): void
    {
        $administrador = User::factory()->administrador()->create();

        $this->actingAs($administrador)
            ->get('/login')
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($administrador)
            ->get('/register')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_business_owner_is_redirected_to_business_dashboard_from_auth_pages(): void
    {
        $dueno = User::factory()->dueno()->create();

        $this->actingAs($dueno)
            ->get('/login')
            ->assertRedirect(route('dashboard'));

        $this->actingAs($dueno)
            ->get('/register')
            ->assertRedirect(route('dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
