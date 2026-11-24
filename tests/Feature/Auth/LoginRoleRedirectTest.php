<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginRoleRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Auth checks require testing + SQLite :memory:.');
        }

        return $app;
    }

    public static function credentials(): array
    {
        return [
            ['admin', false, 'email', '/dashboard'],
            ['admin', false, 'username', '/dashboard'],
            ['cajero', false, 'email', '/dashboard'],
            ['cajero', false, 'username', '/dashboard'],
            ['admin', true, 'email', '/superadmin/dashboard'],
            ['admin', true, 'username', '/superadmin/dashboard'],
        ];
    }

    #[DataProvider('credentials')]
    public function test_login_keeps_identifier_support_and_role_destination(string $role, bool $super, string $identifier, string $destination): void
    {
        $tenant = Tenant::create(['nombre' => 'Prueba de acceso', 'activo' => true]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'rol' => $role, 'es_super_admin' => $super]);

        $this->post('/login', ['email' => $user->{$identifier}, 'password' => 'password'])
            ->assertSessionHasNoErrors()->assertRedirect($destination);
        $this->assertAuthenticatedAs($user);
    }
}
