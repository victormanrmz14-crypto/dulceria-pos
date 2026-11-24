<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NavbarLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Logout tests require testing + SQLite :memory:.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public static function roles(): array
    {
        return [['admin'], ['cajero']];
    }

    #[DataProvider('roles')]
    public function test_real_login_logout_invalidates_session_and_allows_login_again(string $role): void
    {
        $tenant = Tenant::create(['nombre' => 'Prueba', 'activo' => true]);
        $password = Str::random(32);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'rol' => $role, 'password' => Hash::make($password)]);
        $this->post('/login', ['email' => $user->email, 'password' => $password])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->withSession(['private_marker' => 'remove on logout']);
        $oldId = session()->getId();
        $oldToken = session()->token();
        $this->post('/logout')->assertRedirect('/')->assertSessionMissing('private_marker');
        $this->assertGuest();
        $this->assertNotSame($oldId, session()->getId());
        $this->assertNotSame($oldToken, session()->token());
        $this->get('/perfil')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => $password])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->get('/perfil')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_cashier_creation_and_login_keep_existing_access_rules(): void
    {
        $tenant = Tenant::create(['nombre' => 'Prueba', 'activo' => true]);
        $password = Str::random(32);
        $cashierPassword = Str::random(32);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'rol' => 'admin', 'password' => Hash::make($password)]);
        $this->post('/login', ['email' => $admin->email, 'password' => $password]);
        $this->post('/usuarios', ['nombre' => 'Cajero', 'apellido' => 'Prueba', 'username' => 'cashier212',
            'email' => 'cashier212@example.test', 'password' => $cashierPassword,
            'password_confirmation' => $cashierPassword, 'rol' => 'cajero'])->assertRedirect('/usuarios');
        $cashier = User::where('email', 'cashier212@example.test')->firstOrFail();
        $this->assertSame('cajero', $cashier->rol);
        $this->assertSame($tenant->id, $cashier->tenant_id);
        $this->post('/logout');
        $this->post('/login', ['email' => $cashier->email, 'password' => $cashierPassword])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($cashier);
        foreach (['/perfil', '/ventas', '/ventas/historial', '/caja', '/cortes'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/usuarios', '/productos', '/reportes', '/configuracion', '/catalogos/categorias', '/catalogos/marcas', '/catalogos/proveedores'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/configuracion/apariencia')->assertForbidden();
    }
}
