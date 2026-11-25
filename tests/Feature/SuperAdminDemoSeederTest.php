<?php

namespace Tests\Feature;

use App\Models\CorteCaja;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Venta;
use App\Services\SuperAdmin\Analytics;
use Carbon\CarbonImmutable;
use Database\Seeders\SuperAdminDemoSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SuperAdminDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Demo tests require testing + SQLite :memory:.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-24 16:00:00', 'America/Mexico_City'));
    }

    public function test_demo_produces_two_months_of_operations_and_expected_superadmin_scenarios(): void
    {
        $existingAdmin = User::factory()->create(['es_super_admin' => true, 'tenant_id' => null]);
        $password = $existingAdmin->password;
        $this->seed(SuperAdminDemoSeeder::class);
        $this->actingAs($existingAdmin);
        $analytics = new Analytics;

        $this->assertSame(20, Tenant::count());
        $this->assertSame(19, Tenant::where('activo', true)->count());
        $this->assertSame(13, $analytics->sales()->where('created_at', '>=', $analytics->start)->distinct()->count('tenant_id'));
        $this->assertSame(4, Tenant::whereNotIn('id', $analytics->sales()->select('tenant_id'))->count());
        $this->assertSame(6, $analytics->attention(Tenant::query())->count());
        foreach (['sin_productos' => 1, 'sin_primera_venta' => 2, 'sin_ventas_recientes' => 3, 'sin_administrador' => 1] as $type => $count) {
            $this->assertSame($count, $analytics->attention(Tenant::query(), $type)->count(), $type);
        }
        $this->assertSame(374, DB::table('productos')->count());
        $this->assertGreaterThan(3000, Venta::count());
        $this->assertLessThanOrEqual(now()->subMonthsNoOverflow(2), CarbonImmutable::parse(Venta::min('created_at')));
        $this->assertSame('2026-11-24', CarbonImmutable::parse(Venta::max('created_at'))->toDateString());
        foreach (['ventas', 'detalle_ventas', 'movimientos_caja', 'cortes_caja'] as $table) {
            $this->assertSame(0, DB::table($table)->where('created_at', '>', now())->count(), $table);
        }
        $this->assertSame($password, $existingAdmin->fresh()->password);
        $this->assertSame(2, User::where('es_super_admin', true)->count());

        $this->assertSame(0, DB::table('detalle_ventas as d')->join('ventas as v', 'v.id', '=', 'd.venta_id')
            ->join('productos as p', 'p.id', '=', 'd.producto_id')
            ->where(fn ($q) => $q->whereColumn('d.tenant_id', '<>', 'v.tenant_id')->orWhereColumn('d.tenant_id', '<>', 'p.tenant_id'))->count());
        $this->assertSame(0, DB::table('ventas as v')->join('users as u', 'u.id', '=', 'v.user_id')->whereColumn('v.tenant_id', '<>', 'u.tenant_id')->count());
        $mismatches = DB::query()->fromSub(DB::table('detalle_ventas')->selectRaw('venta_id, SUM(importe) AS line_total')->groupBy('venta_id'), 'd')
            ->join('ventas as v', 'v.id', '=', 'd.venta_id')->whereRaw('ABS(d.line_total - v.total) > 0.001')->count();
        $this->assertSame(0, $mismatches);
        $this->assertSame(0, DB::table('detalle_ventas')->whereRaw('ABS(cantidad * precio_unitario - importe) > 0.001')->count());

        $previous = [];
        foreach (CorteCaja::orderBy('fecha_corte')->get() as $cut) {
            $prior = $previous[$cut->user_id] ?? null;
            $sales = Venta::where('user_id', $cut->user_id)->where('created_at', $prior ? '>' : '>=', $cut->fecha_inicio)->where('created_at', '<=', $cut->fecha_corte);
            $movements = DB::table('movimientos_caja')->where('user_id', $cut->user_id)->where('created_at', $prior ? '>' : '>=', $cut->fecha_inicio)->where('created_at', '<=', $cut->fecha_corte);
            $cash = (float) (clone $sales)->where('metodo_pago', 'efectivo')->sum('total')
                + (float) (clone $movements)->where('tipo', 'ingreso')->sum('monto')
                - (float) (clone $movements)->where('tipo', 'retiro')->sum('monto') + ($prior ? (float) $prior->dinero_en_caja : 0);
            $card = (float) (clone $sales)->where('metodo_pago', 'tarjeta')->sum('total');
            $this->assertSame($sales->count(), $cut->num_transacciones);
            $this->assertEqualsWithDelta($cash, (float) $cut->total_efectivo, 0.001);
            $this->assertEqualsWithDelta($card, (float) $cut->total_tarjeta, 0.001);
            $this->assertEqualsWithDelta($cash + $card, (float) $cut->total_general, 0.001);
            $this->assertTrue($cut->fecha_corte->lte(now()));
            $previous[$cut->user_id] = $cut;
        }

        $counts = collect(['tenants', 'users', 'productos', 'ventas', 'detalle_ventas', 'cortes_caja', 'movimientos_caja'])->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()]);
        $this->seed(SuperAdminDemoSeeder::class);
        foreach ($counts as $table => $count) {
            $this->assertSame($count, DB::table($table)->count(), 'Idempotencia: '.$table);
        }
    }

    public function test_demo_refuses_production_and_does_not_insert_anything(): void
    {
        app()->instance('env', 'production');
        try {
            (new SuperAdminDemoSeeder)->run();
            $this->fail('A production demo must be rejected.');
        } catch (\LogicException $exception) {
            $this->assertStringContainsString('local y testing', $exception->getMessage());
            $this->assertSame(0, Tenant::count());
        }
    }

    public function test_collision_rolls_back_the_complete_demo_and_preserves_existing_accounts(): void
    {
        $user = User::factory()->create(['username' => 'demo_admin_05']);
        try {
            $this->seed(SuperAdminDemoSeeder::class);
            $this->fail('Existing usernames must not be overwritten.');
        } catch (QueryException) {
            $this->assertSame(0, Tenant::count());
            $this->assertSame(0, Venta::count());
            $this->assertSame(1, User::count());
            $this->assertSame($user->email, $user->fresh()->email);
        }
    }
}
