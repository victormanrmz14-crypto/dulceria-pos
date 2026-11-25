<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.pages.paths' => [resource_path('js/Pages')]]);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $user = User::withoutGlobalScopes()->where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('admin', $user->rol);
        $this->assertSame('Dulcería Test', Tenant::findOrFail($user->tenant_id)->nombre);
    }

    public function test_registration_preserves_business_and_administrator_names(): void
    {
        $this->post('/register', [
            'negocio' => 'Dulcería de prueba',
            'nombre' => 'Administradora de prueba',
            'email' => 'admin@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors()->assertRedirect('/dashboard');

        $user = User::withoutGlobalScopes()->where('email', 'admin@example.test')->firstOrFail();
        $this->assertSame('admin', $user->rol);
        $this->assertSame('Administradora de prueba', $user->nombre);
        $this->assertSame('Dulcería de prueba', Tenant::findOrFail($user->tenant_id)->nombre);

        $this->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.rol', 'admin')
            ->where('tenantConfig.nombre', 'Dulcería de prueba')
            ->where('flash.bienvenida', true)
            ->where('ventasHoy', 0)
            ->where('productosActivos', 0)
            ->has('ultimasVentas', 0));
    }
}
