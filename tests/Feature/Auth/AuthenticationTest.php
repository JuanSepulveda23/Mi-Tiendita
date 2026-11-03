<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered_via_fortify(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Mi Tiendita');
        $response->assertSee('Iniciar Sesión');
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_admin_user_can_authenticate_via_fortify(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@mitiendita.com',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@mitiendita.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_cashier_user_can_authenticate_via_fortify(): void
    {
        $cashier = User::factory()->create([
            'email' => 'cajero@mitiendita.com',
            'role' => User::ROLE_CASHIER,
        ]);

        $response = $this->post('/login', [
            'email' => 'cajero@mitiendita.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($cashier);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_can_authenticate_with_case_insensitive_email(): void
    {
        $user = User::factory()->create([
            'email' => 'cajero.principal@mitiendita.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'CAJERO.PRINCIPAL@MITIENDITA.COM',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_can_authenticate_with_remember_me(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));

        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authentication_fails_with_non_existent_email_preventing_user_enumeration(): void
    {
        $response = $this->post('/login', [
            'email' => 'no-existe@mitiendita.com',
            'password' => 'random-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        // Confirma que el mensaje de error es el estándar y no revela existencia del usuario
        $errors = session('errors')->get('email');
        $this->assertContains(trans('auth.failed'), $errors);
    }

    public function test_login_validation_requires_email_and_password(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_session_is_regenerated_upon_successful_login(): void
    {
        $user = User::factory()->create();

        $this->get('/login');
        $initialSessionId = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertNotEquals($initialSessionId, session()->getId());
    }

    public function test_rate_limiting_locks_out_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['email' => 'test-throttle@mitiendita.com']);

        // Fortify rate limit por defecto: 5 intentos por minuto
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test-throttle@mitiendita.com',
                'password' => 'wrong-password',
            ]);
            $this->assertGuest();
        }

        // Intento 6 debe ser bloqueado por RateLimiting retornando HTTP 429 (Too Many Requests)
        $response = $this->post('/login', [
            'email' => 'test-throttle@mitiendita.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $this->assertEquals(429, $response->status());
        $this->assertTrue($response->headers->has('Retry-After'));

        // Limpiar para siguientes tests
        RateLimiter::clear(
            \Illuminate\Support\Str::transliterate('test-throttle@mitiendita.com|127.0.0.1')
        );
    }

    public function test_intended_redirect_works_when_accessing_protected_route(): void
    {
        $user = User::factory()->create();

        // Intentar acceder a ruta protegida de caja
        $this->get('/pos')->assertRedirect('/login');

        // Autenticar por la pantalla de login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/pos');
    }

    public function test_already_authenticated_user_redirected_from_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_logout_via_fortify(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
