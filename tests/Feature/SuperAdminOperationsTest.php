<?php

namespace Tests\Feature;

use App\Models\CorteCaja;
use App\Models\Producto;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Venta;
use App\Services\SuperAdmin\Analytics;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SuperAdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Operations tests require testing + SQLite :memory:.');
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

    private function business(string $created = '2026-09-01 10:00:00', bool $enabled = true): Tenant
    {
        $tenant = Tenant::create(['nombre' => 'Dulcería de prueba', 'activo' => $enabled, 'plan' => 'basico']);
        $tenant->forceFill(['created_at' => $created])->save();
        User::factory()->create(['tenant_id' => $tenant->id, 'rol' => 'admin', 'activo' => true]);

        return $tenant;
    }

    private function sale(Tenant $tenant, string $date, string $total = '10.10'): void
    {
        $sale = Venta::create(['tenant_id' => $tenant->id, 'user_id' => $tenant->users()->first()->id,
            'total' => $total, 'metodo_pago' => 'efectivo']);
        $sale->forceFill(['created_at' => $date])->save();
    }

    public function test_new_business_has_pending_milestones_without_premature_attention(): void
    {
        $tenant = $this->business('2026-12-01 09:59:00');
        $tenant->update(['configuracion' => ['negocio' => ['nombre_mostrar' => 'Valor por defecto']]]);
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('stats.productos', 0)->where('stats.primera_venta', null)->where('stats.primer_corte', null)
            ->where('stats.ventas_7', 0)->where('stats.ventas_30', 0)
            ->where('operacion.activacion.0.estado', 'completado')->where('operacion.activacion.1.estado', 'pendiente')
            ->where('operacion.activacion.2.estado', 'pendiente')->where('operacion.activacion.3.estado', 'pendiente')
            ->has('operacion.activacion', 4)->where('operacion.senales', []));
        $this->get('/superadmin/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('kpis.sin_primera_venta', 1)->where('kpis.atencion', 0));
    }

    public function test_products_are_evidence_but_do_not_complete_first_sale(): void
    {
        $tenant = $this->business();
        Producto::create(['tenant_id' => $tenant->id, 'nombre' => 'Chocolate', 'activo' => false]);
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertInertia(fn (Assert $page) => $page
            ->where('stats.productos', 1)->where('operacion.activacion.1.estado', 'completado')
            ->where('operacion.activacion.2.estado', 'pendiente')->has('operacion.senales', 1)
            ->where('operacion.senales.0.tipo', 'sin_primera_venta'));
    }

    public function test_first_sale_recent_windows_and_first_cut_are_scoped_and_exclude_future_dates(): void
    {
        $a = $this->business();
        $b = $this->business();
        $this->sale($a, '2026-10-01 08:00:00', '50.50');
        $this->sale($a, '2026-11-02 00:00:00', '20.20'); // Exact P30 boundary.
        $this->sale($a, '2026-11-25 00:00:00', '30.30'); // Exact P7 boundary.
        $this->sale($a, '2026-12-01 10:00:00', '40.40');
        $this->sale($a, '2026-12-01 10:00:01', '999.99');
        $this->sale($b, '2026-09-01 08:00:00', '100.00');
        foreach (['2026-11-25 18:00:00', '2026-11-20 18:00:00', '2026-12-02 18:00:00'] as $date) {
            CorteCaja::create(['tenant_id' => $a->id, 'user_id' => $a->users()->first()->id,
                'fecha_inicio' => '2026-11-20 08:00:00', 'fecha_corte' => $date]);
        }
        CorteCaja::create(['tenant_id' => $b->id, 'user_id' => $b->users()->first()->id,
            'fecha_inicio' => '2026-09-01 08:00:00', 'fecha_corte' => '2026-09-01 18:00:00']);
        $this->get('/superadmin/dulcerias/'.$a->id)->assertInertia(fn (Assert $page) => $page
            ->where('stats.ventas_total', 4)->where('stats.ventas_30', 3)->where('stats.ventas_7', 2)
            ->where('stats.importe_30', 90.9)->where('stats.primera_venta', '1 oct. 2026, 08:00')
            ->where('stats.ultima_venta', '1 dic. 2026, 10:00')->where('stats.primer_corte', '20 nov. 2026, 18:00')
            ->where('operacion.activacion.2.estado', 'completado')->where('operacion.activacion.3.estado', 'completado')
            ->where('operacion.actividad', 'Con ventas recientes')->has('ultimasVentas', 4));
        $this->get('/superadmin/dulcerias/'.$b->id)->assertInertia(fn (Assert $page) => $page
            ->where('stats.ventas_30', 0)->where('stats.ventas_7', 0)->where('stats.ventas_total', 1)
            ->where('stats.primera_venta', '1 sep. 2026, 08:00')->where('stats.primer_corte', '1 sep. 2026, 18:00'));
        $this->get('/superadmin/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('kpis.con_ventas', 1)->where('kpis.sin_primera_venta', 0)->where('kpis.importe', 90.9));
    }

    public static function saleBoundaries(): array
    {
        return [
            'previous second' => ['2026-11-01 23:59:59', 'Sin ventas en 30 días', 0],
            'first second' => ['2026-11-02 00:00:00', 'Con ventas recientes', 1],
            'month transition' => ['2026-11-30 23:59:59', 'Con ventas recientes', 1],
            'future only' => ['2026-12-01 10:00:01', 'Nunca ha vendido', 0],
        ];
    }

    #[DataProvider('saleBoundaries')]
    public function test_activity_boundaries(string $date, string $activity, int $count): void
    {
        $tenant = $this->business();
        $this->sale($tenant, $date);
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertInertia(fn (Assert $page) => $page
            ->where('operacion.actividad', $activity)->where('stats.ventas_30', $count));
    }

    public function test_never_sold_and_stale_sales_have_different_filters_and_signals(): void
    {
        $never = $this->business();
        $stale = $this->business(enabled: false);
        $this->sale($stale, '2026-10-01 12:00:00');
        foreach (['sin_primera_venta' => $never, 'sin_ventas_recientes' => $stale] as $type => $tenant) {
            $this->get('/superadmin/atencion?tipo='.$type)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->where('tenants.total', 1)->where('tenants.data.0.id', $tenant->id));
        }
    }

    public function test_administrative_plan_expiry_does_not_imply_operational_attention(): void
    {
        $tenant = $this->business();
        $tenant->update(['plan_expira_en' => now()->subDay()]);
        Producto::create(['tenant_id' => $tenant->id, 'nombre' => 'Chocolate']);
        $this->sale($tenant, '2026-12-01 09:00:00');
        $this->get('/superadmin/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('kpis.habilitadas', 1)->where('kpis.con_ventas', 1)->where('kpis.atencion', 0));
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertInertia(fn (Assert $page) => $page
            ->where('tenant.plan_expira_en', '30 nov. 2026')->where('operacion.senales', []));
    }

    public static function onboardingBoundaries(): array
    {
        return ['before threshold' => ['2026-11-24 10:00:01', 0], 'exact threshold' => ['2026-11-24 10:00:00', 1], 'after threshold' => ['2026-11-24 09:59:59', 1]];
    }

    #[DataProvider('onboardingBoundaries')]
    public function test_onboarding_thresholds_are_full_days(string $date, int $count): void
    {
        $this->business($date);
        foreach (['sin_productos', 'sin_primera_venta'] as $type) {
            $this->get('/superadmin/atencion?tipo='.$type)->assertInertia(fn (Assert $page) => $page->where('tenants.total', $count));
        }
    }

    public function test_admin_signal_checks_all_admins_and_attention_is_deduplicated(): void
    {
        $tenant = $this->business();
        $tenant->users()->update(['activo' => false]);
        User::factory()->create(['tenant_id' => $tenant->id, 'rol' => 'cajero', 'activo' => true]);
        $this->get('/superadmin/atencion?tipo=sin_administrador')->assertInertia(fn (Assert $page) => $page
            ->where('tenants.total', 1)->has('tenants.data.0.senales', 3));
        $this->get('/superadmin/dashboard')->assertInertia(fn (Assert $page) => $page->where('kpis.atencion', 1));
        User::factory()->create(['tenant_id' => $tenant->id, 'rol' => 'admin', 'activo' => true]);
        $this->get('/superadmin/atencion?tipo=sin_administrador')->assertInertia(fn (Assert $page) => $page->where('tenants.total', 0));
    }

    public function test_onboarding_thresholds_share_the_same_configuration_in_filters_and_evidence(): void
    {
        config(['superadmin.operations.without_products_days' => 10, 'superadmin.operations.without_first_sale_days' => 14]);
        $tenant = $this->business('2026-11-20 10:00:00');
        $this->get('/superadmin/atencion?tipo=sin_productos')->assertInertia(fn (Assert $page) => $page
            ->where('tenants.total', 1)->has('tenants.data.0.senales', 1)
            ->where('tenants.data.0.senales.0.definicion', 'Registro hace al menos 10 días completos y ningún producto registrado.'));
        $this->get('/superadmin/atencion?tipo=sin_primera_venta')->assertInertia(fn (Assert $page) => $page->where('tenants.total', 0));
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertInertia(fn (Assert $page) => $page->has('operacion.senales', 1));
    }

    public function test_first_cut_does_not_infer_first_sale_and_future_cut_is_not_evidence(): void
    {
        $tenant = $this->business();
        $cut = CorteCaja::create(['tenant_id' => $tenant->id, 'user_id' => $tenant->users()->first()->id,
            'fecha_inicio' => now(), 'fecha_corte' => now()->addDay()]);
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertInertia(fn (Assert $page) => $page->where('stats.primer_corte', null));
        $cut->update(['fecha_corte' => now()]);
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertInertia(fn (Assert $page) => $page
            ->where('operacion.activacion.3.estado', 'completado')->where('operacion.activacion.2.estado', 'pendiente'));
    }

    public function test_missing_registration_date_is_not_marked_complete(): void
    {
        $tenant = $this->business();
        DB::table('tenants')->where('id', $tenant->id)->update(['created_at' => null]);
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertInertia(fn (Assert $page) => $page
            ->where('operacion.activacion.0.estado', 'sin_datos')->where('tenant.creado_en', null));
    }

    public function test_attention_is_paginated_and_type_filter_is_applied_before_pagination(): void
    {
        foreach (range(1, 22) as $i) {
            $this->business();
        }
        $this->get('/superadmin/atencion?tipo=sin_productos&page=2')->assertInertia(fn (Assert $page) => $page
            ->where('tenants.total', 22)->has('tenants.data', 2)->where('tenants.current_page', 2)
            ->where('tenants.first_page_url', fn ($url) => str_contains($url, 'tipo=sin_productos')));
        $this->get('/superadmin/atencion?tipo=desconocido')->assertSessionHasErrors('tipo');
    }

    public function test_no_sales_or_cut_state_column_exists_to_invent_cancellation_filter(): void
    {
        $this->assertFalse(Schema::hasColumn('ventas', 'estado'));
        $this->assertFalse(Schema::hasColumn('ventas', 'cancelada'));
        $this->assertFalse(Schema::hasColumn('ventas', 'deleted_at'));
    }

    public function test_query_failures_return_unavailable_instead_of_zero_metrics(): void
    {
        $tenant = $this->business();
        Schema::drop('ventas');
        $this->get('/superadmin/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page->where('kpis', null)->where('grafica', null));
        $this->get('/superadmin/atencion')->assertOk()->assertInertia(fn (Assert $page) => $page->where('tenants', null));
        $this->get('/superadmin/dulcerias/'.$tenant->id)->assertOk()->assertInertia(fn (Assert $page) => $page->where('stats', null)->where('operacion', null));
    }

    public function test_queries_do_not_grow_with_number_of_businesses(): void
    {
        $this->business();
        DB::enableQueryLog();
        $analytics = new Analytics;
        $analytics->attention($analytics->tenants())->limit(20)->get()->map(fn ($t) => $analytics->row($t));
        $single = count(DB::getQueryLog());
        DB::disableQueryLog();
        foreach (range(1, 8) as $i) {
            $this->business();
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $analytics->attention($analytics->tenants())->limit(20)->get()->map(fn ($t) => $analytics->row($t));
        $this->assertSame($single, count(DB::getQueryLog()));
        $this->assertSame(2, $single);
        DB::disableQueryLog();
    }

    public function test_tenant_cannot_access_global_operations_or_another_business_by_parameters(): void
    {
        $own = $this->business();
        $other = $this->business();
        $this->actingAs($own->users()->first());
        foreach (['/superadmin/dashboard?tenant_id='.$other->id, '/superadmin/atencion?tipo=sin_productos&tenant_id='.$other->id,
            '/superadmin/dulcerias/'.$other->id, '/superadmin/dulcerias?estado=atencion'] as $url) {
            $this->get($url)->assertForbidden();
        }
        auth()->logout();
        $this->get('/superadmin/atencion')->assertRedirect('/login');
    }
}
