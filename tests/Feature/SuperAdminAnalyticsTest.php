<?php

namespace Tests\Feature;

use App\Models\Anuncio;
use App\Models\AuditLog;
use App\Models\Producto;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Venta;
use App\Services\SuperAdmin\HealthChecks;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SuperAdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Analytics tests require testing + SQLite :memory:.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['inertia.pages.paths' => [resource_path('js/Pages')]]);
        $this->travelTo(CarbonImmutable::parse('2026-12-01 10:00:00', 'America/Mexico_City'));
        $this->actingAs(User::factory()->create(['es_super_admin' => true, 'tenant_id' => null]));
    }

    private function tenant(string $name, bool $enabled = true, string $created = '2026-09-01', string $plan = 'trial'): Tenant
    {
        $tenant = Tenant::create(['nombre' => $name, 'activo' => $enabled, 'plan' => $plan]);
        $tenant->forceFill(['created_at' => $created])->save();

        return $tenant;
    }

    private function admin(Tenant $tenant, bool $enabled = true): User
    {
        return User::factory()->create(['tenant_id' => $tenant->id, 'rol' => 'admin', 'activo' => $enabled]);
    }

    private function sale(Tenant $tenant, User $user, string $date, string $amount): void
    {
        $sale = Venta::create(['tenant_id' => $tenant->id, 'user_id' => $user->id, 'total' => $amount, 'metodo_pago' => 'efectivo']);
        $sale->forceFill(['created_at' => $date])->save();
    }

    private function scenario(): array
    {
        $recent = $this->tenant('Reciente', plan: 'pro');
        $this->admin($recent, false); // An older disabled admin must not hide an enabled admin.
        $admin = $this->admin($recent);
        $this->sale($recent, $admin, '2026-11-30 23:59:59', '10.10');
        $this->sale($recent, $admin, '2026-11-02 00:00:00', '20.20');
        $this->sale($recent, $admin, '2026-11-01 23:59:59', '99.99');
        $this->sale($recent, $admin, '2026-12-01 10:00:01', '999.99'); // Future excluded.
        $old = $this->tenant('Última en octubre', plan: 'basico');
        $this->sale($old, $this->admin($old), '2026-10-15 12:00:00', '50.00');
        $never = $this->tenant('Nunca vendió');
        $this->admin($never);
        $new = $this->tenant('Recién registrada', created: '2026-11-25');
        $this->admin($new);
        $disabled = $this->tenant('Deshabilitada con venta', false, plan: 'basico');
        $user = User::factory()->create(['tenant_id' => $disabled->id, 'rol' => 'cajero']);
        $this->sale($disabled, $user, '2026-11-30 12:00:00', '30.30');
        $expired = $this->tenant('Plan vencido', plan: 'pro');
        $this->admin($expired);
        $expired->update(['plan_expira_en' => now()]);
        foreach ([$recent, $old, $never, $disabled, $expired] as $tenant) {
            Producto::create(['tenant_id' => $tenant->id, 'nombre' => 'Producto registrado']);
        }

        return compact('recent', 'old', 'never', 'new', 'disabled', 'expired');
    }

    public function test_empty_dashboard_has_zero_counts_and_thirty_zero_days(): void
    {
        $this->get('/superadmin/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('SuperAdmin/Dashboard')->where('kpis', ['registradas' => 0, 'habilitadas' => 0,
                'con_ventas' => 0, 'sin_primera_venta' => 0, 'nuevas' => 0, 'atencion' => 0, 'ventas' => 0, 'importe' => 0])
            ->has('grafica', 30)->where('grafica.0.fecha', '2026-11-02')
            ->where('grafica.29.fecha', '2026-12-01')->where('grafica.0.ventas', 0)->has('alertas', 0));
    }

    public function test_dashboard_separates_enabled_activity_new_and_distinct_attention(): void
    {
        $this->scenario();
        $this->get('/superadmin/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('kpis.registradas', 6)->where('kpis.habilitadas', 5)->where('kpis.con_ventas', 2)
            ->where('kpis.nuevas', 1)->where('kpis.atencion', 4)->where('kpis.ventas', 3)
            ->where('kpis.importe', 60.6)->where('periodo.zona', 'America/Mexico_City')
            ->where('grafica.0.ventas', 1)->where('grafica.28.negocios', 2)->where('grafica.28.ventas', 2)
            ->where('grafica.28.importe', 40.4)->where('grafica.29.ventas', 0)->has('alertas', 4));
    }

    public function test_monitoring_reads_last_sale_from_all_history_and_never_is_distinct(): void
    {
        $this->scenario();
        $this->get('/superadmin/monitoreo')->assertOk()->assertInertia(function (Assert $page) {
            $page->where('tenants.total', 6)->where('jobsFallidos', 0);
            $rows = collect($page->toArray()['props']['tenants']['data'])->keyBy('nombre');
            $this->assertSame('15 oct. 2026, 12:00', $rows['Última en octubre']['ultima_venta']);
            $this->assertSame('Sin ventas en 30 días', $rows['Última en octubre']['actividad']);
            $this->assertNull($rows['Nunca vendió']['ultima_venta']);
            $this->assertSame('Nunca ha vendido', $rows['Nunca vendió']['actividad']);
            $this->assertSame('Con ventas recientes', $rows['Reciente']['actividad']);
        });
    }

    public function test_directory_filters_all_data_and_prefers_enabled_admin(): void
    {
        $this->scenario();
        $this->get('/superadmin/dulcerias?actividad=sin_recientes')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('tenants.total', 1)->where('tenants.data.0.nombre', 'Última en octubre'));
        $this->get('/superadmin/dulcerias?q=Reciente&estado=habilitadas')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('tenants.total', 1)->where('tenants.data.0.admin_activo', true)->where('tenants.data.0.problemas', []));
        $this->get('/superadmin/dulcerias?estado=atencion')->assertOk()->assertInertia(fn (Assert $page) => $page->where('tenants.total', 4));
        $this->get('/superadmin/dulcerias?estado=ok')->assertOk()->assertInertia(fn (Assert $page) => $page->where('tenants.total', 2));
    }

    public function test_detail_amount_and_chart_are_scoped_to_the_selected_tenant(): void
    {
        ['recent' => $recent, 'old' => $old] = $this->scenario();
        $this->get('/superadmin/dulcerias/'.$recent->id)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('stats.ventas_30', 2)->where('stats.ventas_total', 3)->where('stats.importe_30', 30.3)
            ->where('admin.activo', true)->where('grafica.28.ventas', 1)->has('ultimasVentas', 3));
        $this->get('/superadmin/dulcerias/'.$old->id)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('stats.ventas_30', 0)->where('stats.ventas_total', 1)->where('stats.importe_30', 0)
            ->where('stats.ultima_venta', '15 oct. 2026, 12:00'));
    }

    public function test_plan_distribution_has_no_fabricated_mrr_and_valid_input_dates(): void
    {
        $this->scenario();
        $this->get('/superadmin/saas')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('resumen.trial', 2)->where('resumen.basico', 2)->where('resumen.pro', 2)
            ->where('resumen.vencidos', 1)->missing('resumen.mrr')->where('tenants.data', fn ($rows) => collect($rows)->firstWhere('nombre', 'Plan vencido')['plan_expira_input'] === '2026-12-01'));
    }

    public function test_directory_is_paginated_and_search_is_not_limited_to_first_page(): void
    {
        foreach (range(1, 24) as $i) {
            $this->tenant('Negocio '.$i);
        }
        $this->get('/superadmin/dulcerias')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('tenants.data', 20)->where('tenants.total', 24)->where('tenants.last_page', 2));
        $this->get('/superadmin/dulcerias?q=Negocio%201')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('tenants.total', 11));
        $this->get('/superadmin/dulcerias?q=0')->assertOk()->assertInertia(fn (Assert $page) => $page->where('tenants.total', 2));
    }

    public function test_global_users_counts_and_role_filters_are_independent_of_pagination(): void
    {
        $a = $this->tenant('Negocio A');
        $b = $this->tenant('Negocio B');
        $this->admin($a);
        User::factory()->create(['tenant_id' => $b->id, 'rol' => 'cajero', 'activo' => false]);
        $this->get('/superadmin/usuarios?rol=cajero&estado=deshabilitado')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('totalUsuarios', 3)->where('totalActivos', 2)->where('totalAdmins', 1)
            ->where('usuarios.total', 1)->where('usuarios.data.0.tenant_nombre', 'Negocio B'));
    }

    public function test_audit_search_uses_history_and_exposes_existing_metadata(): void
    {
        AuditLog::create(['user_id' => auth()->id(), 'accion' => 'plan_cambio', 'descripcion' => 'Cambio verificable', 'meta' => ['plan_nuevo' => 'pro']]);
        foreach (range(1, 30) as $i) {
            AuditLog::create(['accion' => 'login', 'descripcion' => 'Acceso '.$i]);
        }
        $this->get('/superadmin/auditoria?accion=plan_cambio')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('logs.total', 1)->where('logs.data.0.meta.plan_nuevo', 'pro'));
    }

    public function test_announcement_count_uses_all_pages_and_expiration(): void
    {
        foreach (range(1, 21) as $i) {
            Anuncio::create(['titulo' => 'Anuncio '.$i, 'cuerpo' => 'Mensaje', 'tipo' => 'info', 'activo' => true, 'creado_por' => auth()->id()]);
        }
        Anuncio::create(['titulo' => 'Expirado', 'cuerpo' => 'Mensaje', 'tipo' => 'info', 'activo' => true, 'creado_por' => auth()->id(), 'expira_en' => now()->subSecond()]);
        $this->get('/superadmin/comunicacion')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('vigentes', 21)->where('anuncios.total', 22)->has('anuncios.data', 20));
    }

    public function test_health_snapshot_checks_actual_database_cache_and_failed_jobs(): void
    {
        DB::table('failed_jobs')->insert(['uuid' => 'test-214', 'connection' => 'database', 'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\Example']), 'exception' => "RuntimeException: Prueba\nDetails", 'failed_at' => now()]);
        $snapshot = (new HealthChecks)->snapshot();
        $this->assertTrue($snapshot['salud'][0]['ok']);
        $this->assertTrue($snapshot['salud'][1]['ok']);
        $this->assertStringContainsString('array', $snapshot['salud'][1]['nombre']);
        $this->assertSame(1, $snapshot['jobsFallidos']);
        $this->assertFalse($snapshot['salud'][2]['ok']);
        $this->assertSame('Example', $snapshot['failedJobs'][0]['job']);
    }

    public function test_unavailable_failed_jobs_is_unknown_rather_than_zero(): void
    {
        Schema::drop('failed_jobs');
        $snapshot = (new HealthChecks)->snapshot();
        $this->assertNull($snapshot['jobsFallidos']);
        $this->assertNull($snapshot['salud'][2]['ok']);
    }

    public function test_database_and_cache_failure_are_not_reported_healthy(): void
    {
        DB::partialMock()->shouldReceive('select')->with('SELECT 1')->once()->andThrow(new \RuntimeException('Unavailable'));
        Cache::shouldReceive('put')->once()->andThrow(new \RuntimeException('Unavailable'));
        Cache::shouldReceive('forget')->once()->andReturn(true);
        $snapshot = (new HealthChecks)->snapshot();
        $this->assertFalse($snapshot['salud'][0]['ok']);
        $this->assertFalse($snapshot['salud'][1]['ok']);
    }

    public function test_metrics_routes_preserve_superadmin_requirement(): void
    {
        $tenant = $this->tenant('Cuenta tenant');
        $this->actingAs($this->admin($tenant));
        $this->get('/superadmin/dashboard')->assertForbidden();
    }
}
