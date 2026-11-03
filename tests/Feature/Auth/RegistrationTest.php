<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_render_registration_screen(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect('/login');
    }

    public function test_cashier_cannot_render_registration_screen(): void
    {
        $cashier = User::factory()->cashier()->create();

        $response = $this->actingAs($cashier)->get('/register');

        $response->assertStatus(403);
    }

    public function test_admin_can_render_registration_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/register');

        $response->assertStatus(200);
    }

    public function test_admin_can_register_new_cashier_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/register', [
            'name' => 'Empleado Cajero',
            'email' => 'cajero.nuevo@mitiendita.com',
            'role' => User::ROLE_CASHIER,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'cajero.nuevo@mitiendita.com',
            'role' => User::ROLE_CASHIER,
        ]);

        // El creador continúa autenticado como admin
        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('register'));
        $response->assertSessionHas('status');
    }
}
