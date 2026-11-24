<?php

namespace App\Services\SuperAdmin;

use App\Models\CorteCaja;
use App\Models\Producto;
use App\Models\Tenant;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/** Read-only aggregates. The existing tenant scope and route authorization remain in force. */
class Analytics
{
    public readonly CarbonImmutable $now;

    public readonly CarbonImmutable $start;

    public function __construct()
    {
        $this->now = CarbonImmutable::now();
        $this->start = $this->now->subDays(config('superadmin.operations.activity_days') - 1)->startOfDay();
    }

    public function period(): array
    {
        return ['inicio' => $this->start->toDateTimeString(), 'fin' => $this->now->toDateTimeString(),
            'zona' => config('app.timezone'), 'label' => $this->start->isoFormat('D MMM').' – '.$this->now->isoFormat('D MMM YYYY')];
    }

    public function sales(): Builder
    {
        return Venta::query()->whereNotNull('tenant_id')
            ->whereIn('tenant_id', Tenant::select('id'))->where('created_at', '<=', $this->now);
    }

    public function tenants(): Builder
    {
        return Tenant::query()->withCount('users')->withCount(['users as admins_habilitados' => fn ($q) => $q->where('rol', 'admin')->where('activo', true)])
            ->with(['users' => fn ($q) => $q->select(['id', 'tenant_id', 'nombre', 'apellido', 'email', 'activo', 'created_at'])->where('rol', 'admin')->orderByDesc('activo')->orderBy('created_at')->orderBy('id')->limit(1)])
            ->addSelect(['productos_count' => Producto::selectRaw('COUNT(*)')->whereColumn('productos.tenant_id', 'tenants.id')])
            ->addSelect(['primera_venta' => $this->sales()->selectRaw('MIN(created_at)')->whereColumn('ventas.tenant_id', 'tenants.id')])
            ->addSelect(['ultima_venta' => $this->sales()->selectRaw('MAX(created_at)')->whereColumn('ventas.tenant_id', 'tenants.id')])
            ->addSelect(['ventas_30' => $this->sales()->selectRaw('COUNT(*)')->whereColumn('ventas.tenant_id', 'tenants.id')->where('created_at', '>=', $this->start)]);
    }

    public function criteria(): array
    {
        return [
            ['tipo' => 'sin_productos', 'label' => 'Sin productos', 'definicion' => 'Registro hace al menos '.config('superadmin.operations.without_products_days').' días completos y ningún producto registrado.'],
            ['tipo' => 'sin_primera_venta', 'label' => 'Sin primera venta', 'definicion' => 'Registro hace al menos '.config('superadmin.operations.without_first_sale_days').' días completos y ninguna venta válida en su historial.'],
            ['tipo' => 'sin_ventas_recientes', 'label' => 'Sin ventas recientes', 'definicion' => 'Vendió anteriormente, pero no tiene ventas válidas en los últimos '.config('superadmin.operations.activity_days').' días.'],
            ['tipo' => 'sin_administrador', 'label' => 'Sin administrador habilitado', 'definicion' => 'No tiene ninguna cuenta con rol administrador y estado habilitado.'],
        ];
    }

    public function attention(Builder $query, ?string $type = null): Builder
    {
        $types = $type ? [$type] : array_column($this->criteria(), 'tipo');

        return $query->where(function ($q) use ($types) {
            foreach ($types as $type) {
                $q->orWhere(fn ($signal) => $this->attentionType($signal, $type));
            }
        });
    }

    private function attentionType(Builder $query, string $type): void
    {
        match ($type) {
            'sin_productos' => $query->where('tenants.created_at', '<=', $this->now->subDays(config('superadmin.operations.without_products_days')))
                ->whereNotIn('tenants.id', Producto::select('tenant_id')->whereNotNull('tenant_id')),
            'sin_primera_venta' => $query->where('tenants.created_at', '<=', $this->now->subDays(config('superadmin.operations.without_first_sale_days')))
                ->whereNotIn('tenants.id', $this->sales()->select('tenant_id')),
            'sin_ventas_recientes' => $query->whereIn('tenants.id', $this->sales()->select('tenant_id'))
                ->whereNotIn('tenants.id', $this->sales()->select('tenant_id')->where('created_at', '>=', $this->start)),
            'sin_administrador' => $query->whereDoesntHave('users', fn ($u) => $u->where('rol', 'admin')->where('activo', true)),
            default => throw new \InvalidArgumentException('Unknown operational signal.'),
        };
    }

    public function signals(Tenant $t): array
    {
        $signals = [];
        $evidence = [
            'sin_productos' => ! $t->productos_count && $t->created_at?->lte($this->now->subDays(config('superadmin.operations.without_products_days')))
                ? 'Registro: '.$this->date($t->created_at).'. Productos registrados: 0.' : null,
            'sin_primera_venta' => ! $t->primera_venta && $t->created_at?->lte($this->now->subDays(config('superadmin.operations.without_first_sale_days')))
                ? 'Registro: '.$this->date($t->created_at).'. No existe una venta válida hasta la consulta.' : null,
            'sin_ventas_recientes' => $t->ultima_venta && ! $t->ventas_30
                ? 'Última venta: '.$this->date($t->ultima_venta).'. Inicio del período: '.$this->date($this->start).'.' : null,
            'sin_administrador' => ! $t->admins_habilitados ? 'Administradores habilitados: 0. Se revisan todas las cuentas con rol administrador.' : null,
        ];
        foreach ($this->criteria() as $criterion) {
            if ($evidence[$criterion['tipo']] !== null) {
                $signals[] = [...$criterion, 'evidencia' => $evidence[$criterion['tipo']]];
            }
        }

        return $signals;
    }

    public function date(mixed $date): ?string
    {
        return $date ? CarbonImmutable::parse($date)->isoFormat('D MMM YYYY, HH:mm') : null;
    }

    public function activation(Tenant $t): array
    {
        $firstCut = CorteCaja::where('tenant_id', $t->id)->where('fecha_corte', '<=', $this->now)->min('fecha_corte');

        return [
            ['clave' => 'registro', 'label' => 'Negocio registrado', 'definicion' => 'Existe una fecha de creación del negocio.', 'estado' => $t->created_at ? 'completado' : 'sin_datos', 'evidencia' => $this->date($t->created_at)],
            ['clave' => 'productos', 'label' => 'Productos registrados', 'definicion' => 'Existe al menos un producto actualmente asociado al negocio, habilitado o deshabilitado.', 'estado' => $t->productos_count ? 'completado' : 'pendiente', 'evidencia' => $t->productos_count.($t->productos_count == 1 ? ' producto registrado' : ' productos registrados')],
            ['clave' => 'venta', 'label' => 'Primera venta', 'definicion' => 'Primera venta válida en el historial hasta la hora de consulta.', 'estado' => $t->primera_venta ? 'completado' : 'pendiente', 'evidencia' => $this->date($t->primera_venta)],
            ['clave' => 'corte', 'label' => 'Primer corte de caja', 'definicion' => 'Existe un corte del negocio con fecha de corte igual o anterior a la consulta.', 'estado' => $firstCut ? 'completado' : 'pendiente', 'evidencia' => $this->date($firstCut)],
        ];
    }

    public function row(Tenant $t): array
    {
        $admin = $t->users->first();
        $last = $t->ultima_venta ? CarbonImmutable::parse($t->ultima_venta) : null;
        $signals = $this->signals($t);
        $issues = array_column($signals, 'label');

        return [
            'id' => $t->id, 'nombre' => $t->nombre, 'activo' => $t->activo, 'plan' => $t->plan,
            'admin_nombre' => $admin ? trim($admin->nombre.' '.$admin->apellido) : null,
            'admin_email' => $admin?->email, 'admin_activo' => $admin?->activo,
            'usuarios' => $t->users_count, 'admins_habilitados' => (int) $t->admins_habilitados,
            'ventas_30' => (int) $t->ventas_30, 'ultima_venta' => $last?->isoFormat('D MMM YYYY, HH:mm'),
            'productos' => (int) $t->productos_count, 'primera_venta' => $this->date($t->primera_venta), 'senales' => $signals,
            'actividad' => ! $last ? 'Nunca ha vendido' : ($t->ventas_30 ? 'Con ventas recientes' : 'Sin ventas en 30 días'),
            'problemas' => $issues, 'estado' => $issues ? 'problema' : 'ok',
            'creado_en' => $this->date($t->created_at),
        ];
    }

    public function series(?int $tenantId = null): array
    {
        $sales = $this->sales()->where('created_at', '>=', $this->start);
        if ($tenantId !== null) {
            $sales->where('tenant_id', $tenantId);
        }
        $sales = $sales->selectRaw('DATE(created_at) as fecha, COUNT(*) as ventas, COUNT(DISTINCT tenant_id) as negocios, SUM(total) as importe')->groupBy('fecha')->get()->keyBy('fecha');
        $registrations = $tenantId === null
            ? Tenant::whereBetween('created_at', [$this->start, $this->now])
                ->selectRaw('DATE(created_at) as fecha, COUNT(*) as registros')->groupBy('fecha')->get()->keyBy('fecha')
            : collect();

        return collect(range(0, config('superadmin.operations.activity_days') - 1))->map(function ($i) use ($sales, $registrations) {
            $day = $this->start->addDays($i)->toDateString();
            $sale = $sales->get($day);

            return ['fecha' => $day, 'registros' => (int) ($registrations->get($day)?->registros ?? 0),
                'ventas' => (int) ($sale?->ventas ?? 0), 'negocios' => (int) ($sale?->negocios ?? 0), 'importe' => round((float) ($sale?->importe ?? 0), 2)];
        })->all();
    }
}
